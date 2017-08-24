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
    var $ip;
    var $port;
    var $token;
    var $name;

    public function __construct($dbh, $motdh)
    {
        $this->dbh = $dbh;
        $this->motdh = $motdh;
        
        if ($this->motdh->get_script_filename() == "redirect.php") {
            $this->ip = filter_input(INPUT_GET, "serverip", FILTER_VALIDATE_IP);
            $this->port = filter_input(INPUT_GET, "serverport", FILTER_VALIDATE_INT);
            $this->token = $this->get_token();
        } else if ($this->motdh->get_script_filename() == "register.php") {
            $this->ip = filter_input(INPUT_POST, "serverip", FILTER_VALIDATE_IP);
            $this->port = filter_input(INPUT_POST, "serverport", FILTER_VALIDATE_INT);
            $this->token = filter_input(INPUT_POST, "servertoken", FILTER_SANITIZE_STRING);
            $this->name = filter_input(INPUT_POST, "servername", FILTER_SANITIZE_STRING);
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
                        $this->token,
                        false,
                        "Server Registered Sucessfully. Keep the server token in a safe place.",
                        true);
                } else {
                    $this->motdh->create_response(
                        $this->token,
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
        return $this->ip &&
            ($this->port > 0 && $this->port <= 65535) &&
            ($this->ip == $this->motdh->get_real_ip()) &&
            ($this->name) &&
            ($check_blocked ? !$this->is_blocked() : true);
    }

    public function is_blocked ()
    {
        $result = $this->dbh->query("SELECT is_blocked FROM ".SERVERS_TABLE_NAME." WHERE ip = :ip AND port = :port")
                ->bind(":ip", $this->ip)
                ->bind(":port", $this->port)
                ->single();
        return $result["is_blocked"];
    }

    public function is_in_db ()
    {
        return $this->dbh->query("SELECT ind FROM ".SERVERS_TABLE_NAME." WHERE ip = :ip AND port = :port")
                ->bind(":ip", $this->ip)
                ->bind(":port", $this->port)
                ->executeRows();
    }

    public function get_token ()
    {
        $result = $this->dbh->query("SELECT token FROM ".SERVERS_TABLE_NAME." WHERE ip = :ip AND port = :port")
                ->bind(":ip", $this->ip)
                ->bind(":port", $this->port)
                ->single();
        return $result["token"];
    }

    public function is_token_valid ()
    {
        return $this->get_token() == $this->token;
    }

    public function generate_token ()
    {
        $keymap = "01234567890ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrustuvwxyz";
        $this->token = "";

        $charlen = strlen($keymap) - 1;

        for ($index = 0; $index < AUTH_TOKEN_SIZE; $index++) {
            $this->token .= $keymap[mt_rand(0, $charlen)];
        }

        return $this->token;
    }

    public function add_to_db ()
    {
        return $this->dbh->query("INSERT INTO ".SERVERS_TABLE_NAME." (name, ip, port, token, is_blocked, created_at) VALUES (:name, :ip, :port, :token, :is_blocked, :created_at)")
            ->bind(":name", $this->name)
            ->bind(":ip", $this->ip)
            ->bind(":port", $this->port)
            ->bind(":token", $this->token)
            ->bind(":is_blocked", false)
            ->bind(":created_at", time())
            ->execute();
    }

    public function cleanup_db ()
    {
        // Cleanup old server entries - anything over 90 days
        $count = $this->dbh->query("DELETE FROM ".SERVERS_TABLE_NAME." WHERE created_at <= :created_at")
            ->bind (":created_at", time() + 7776000)
            ->resultsetNum();
        printf("Deleted %d old server entries from  the database\n", $count);

        // Cleanup old link entries - anything older than 1 hour
        $count = $this->dbh->query("DELETE FROM ".LINKS_TABLE_NAME." WHERE created_at <= :created_at")
            ->bind (":created_at", time() + 36400)
            ->resultsetNum();
        printf("Deleted %d old links entries from the database\n", $count);
    }
}

?>
