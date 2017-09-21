<?php
/*
 * MOTD Fixer Server Script
 *
 * Fixes the MOTD loading of data under Counter-Strike : Global Offensive
 *
 * Coded by dubbeh - www.dubbeh.net
 *
 * Licensed under the GPLv3
 *
 */

class MOTDServer
{
    private $dbh;
    private $motdh;
    var $real_ip;
    var $sent_ip;
    var $sent_port;
    var $server_token;
    var $server_name;

    public function __construct($dbh, $motdh)
    {
        $this->dbh = $dbh;
        $this->motdh = $motdh;
        $this->real_ip = $this->motdh->get_real_ip();
        $this->sent_ip = filter_input(INPUT_POST, "serverip", FILTER_VALIDATE_IP);
        $this->sent_port = filter_input(INPUT_POST, "serverport", FILTER_VALIDATE_INT);
        
        if ($_SERVER["PHP_SELF"] == "redirect.php") {
            $this->server_token = $this->get_token();
        } else if ($_SERVER["PHP_SELF"] == "register.php" || $_SERVER["PHP_SELF"] == "delete.php") {
            $this->server_token = filter_input(INPUT_POST, "servertoken", FILTER_SANITIZE_STRING);
            $this->server_name = filter_input(INPUT_POST, "servername", FILTER_SANITIZE_STRING);
        } else if ($_SERVER["PHP_SELF"] == "motdf_cron.php") {
            printf("Running database cleanup cronjob.\n");
        } else {
            $this->motdh->create_response(
                0,
                false,
                "Server :: Unknown input",
                false
            );
        }
    }

    public function register ()
    {
        if ($this->is_valid(false)) {
            if ($this->is_in_db() && !$this->is_blocked()) {
                    $this->motdh->create_response(
                    $this->get_token(),
                    false,
                    "Server already found in DB. Sending current token.",
                    true);
            } else if ($this->is_in_db() && $this->is_blocked()) {
                    $this->motdh->create_response(
                    0,
                    true,
                    "Server appears to be blocked. Possibly for abuse?",
                    false);
            } else if (!$this->is_in_db()) {
                if ($this->generate_token() && $this->add_to_db()) {
                    $this->motdh->create_response(
                        $this->server_token,
                        false,
                        "Server Registered Sucessfully. Keep the server token in a safe place.",
                        true);
                } else {
                    $this->motdh->create_response(
                        $this->server_token,
                        false,
                        "Error Registering server or adding to the database in the final stage.",
                        false);
                }
            } else {
                $this->motdh->create_response(
                    0,
                    false,
                    "Server registration error.",
                    false);
            }
        } else {
            $this->motdh->create_response(
                    0,
                    false,
                    "Server not valid.",
                    false);
        }
    }

    public function is_valid ($check_blocked)
    {
        return $this->real_ip &&
            $this->sent_ip &&
            ($this->sent_port > 0 && $this->sent_port <= 65535) &&
            ($this->server_name) &&
            ($check_blocked ? !$this->is_blocked() : true);
    }

    public function is_blocked ()
    {
        $result = $this->dbh->query("SELECT is_blocked FROM ".SERVERS_TABLE_NAME.
            " WHERE".
            " sent_ip = :sent_ip".
            " AND".
            " sent_port = :sent_port".
            " AND".
            " real_ip = :real_ip")
            ->bind(":sent_ip", $this->sent_ip)
            ->bind(":sent_port", $this->sent_port)
            ->bind(":real_ip", $this->real_ip)
            ->single();
        if ($result) {
            return $result["is_blocked"];
        } else {
            return false;
        }
    }

    public function is_in_db ()
    {
        $result = $this->dbh->query("SELECT ind FROM ".SERVERS_TABLE_NAME.
            " WHERE".
            " sent_ip = :sent_ip".
            " AND".
            " sent_port = :sent_port".
            " AND".
            " real_ip = :real_ip")
            ->bind(":sent_ip", $this->sent_ip)
            ->bind(":sent_port", $this->sent_port)
            ->bind(":real_ip", $this->real_ip)
            ->executeRows();
        if ($result) {
            return true;
        } else {
            return false;
        }
    }

    public function get_token ()
    {
        $result = $this->dbh->query("SELECT server_token FROM ".SERVERS_TABLE_NAME.
            " WHERE".
            " sent_ip = :sent_ip".
            " AND".
            " sent_port = :sent_port".
            " AND".
            " real_ip = :real_ip")
            ->bind(":sent_ip", $this->sent_ip)
            ->bind(":sent_port", $this->sent_port)
            ->bind(":real_ip", $this->real_ip)
            ->single();
        if ($result) {
            return $result["server_token"];
        } else {
            return "";
        }
    }

    public function is_token_valid ()
    {
        if (AUTH_TYPE == AUTH_REGISTRATION) {
            return $this->get_token() == $this->server_token;
        } else if  (AUTH_TYPE == AUTH_IP) {
            return true;
        }

        return false;
    }

    public function generate_token ()
    {
        $keymap = "01234567890ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrustuvwxyz";
        $this->server_token = "";

        $charlen = strlen($keymap) - 1;

        for ($index = 0; $index < AUTH_TOKEN_SIZE; $index++) {
            $this->server_token .= $keymap[mt_rand(0, $charlen)];
        }

        return $this->server_token;
    }

    public function add_to_db ()
    {
        return $this->dbh->query("INSERT INTO ".SERVERS_TABLE_NAME.
                " (server_name, sent_ip, sent_port, real_ip, server_token, is_blocked, created_at)".
                " VALUES".
                " (:server_name, :sent_ip, :sent_port, :real_ip, :server_token, :is_blocked, :created_at)")
            ->bind(":server_name", $this->server_name)
            ->bind(":sent_ip", $this->sent_ip)
            ->bind(":sent_port", $this->sent_port)
            ->bind(":real_ip", $this->real_ip)
            ->bind(":server_token", $this->server_token)
            ->bind(":is_blocked", false)
            ->bind(":created_at", time())
            ->execute();
    }

    public function cleanup_db ()
    {
        // Cleanup old link entries - anything older than 1 hour
        $count = $this->dbh->query("DELETE FROM ".LINKS_TABLE_NAME.
            " WHERE".
            " created_at <= :created_at")
            ->bind (":created_at", time() + 3600)
            ->executeRows();
        printf("Deleted %d old link entries from the database\n", $count);
    }

    public function increment_hits()
    {
        $result = $this->dbh->query("SELECT * FROM ".SERVERS_TABLE_NAME.
            " WHERE".
            " sent_ip = :sent_ip".
            " AND".
            " sent_port = :sent_port".
            " AND".
            " server_token = :server_token")
            ->bind(":sent_ip", $this->sent_ip)
            ->bind(":sent_port", $this->sent_port)
            ->bind(":server_token", $this->server_token)
            ->single();

        if ($result)
        {
            $this->dbh->query("UPDATE ".SERVERS_TABLE_NAME.
                " SET hits = :hits".
                " WHERE ind = :ind")
            ->bind(":hits", $result['hits'] + 1)
            ->bind(":ind", $result['ind'])
            ->execute();
        }
    }
}

?>
