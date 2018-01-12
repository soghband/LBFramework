<?php
    class Session {
        static function start() {
            ini_set("session.use_cookies", 1);
            ini_set("session.cookie_httponly", 1);
            if (SESSION_SECURE) {
                ini_set("session.cookie_secure", 1);
            }
            session_start();
        }
        static function get($dataName = "") {
            if ($dataName == "") {
                return $_SESSION;
            } else {
                if (isset($_SESSION[$dataName])) {
                    return $_SESSION[$dataName];
                } else {
                    return "";
                }
            }
        }
        static function set($dataName="",$value="") {
            if ($dataName != "") {
                if ($value=="") {
                    if (isset($_SESSION[$dataName])) {
                        unset($_SESSION[$dataName]);
                        return true;
                    }
                } else {
                    $_SESSION[$dataName] = $value;
                    return true;
                }
            }
            return false;
        }
        static function getByPage($dataName = "") {
            $pageHash = View::getPageHash();
            if ($dataName == "") {
                return $_SESSION[$pageHash];
            } else {
                if (isset($_SESSION[$pageHash][$dataName])) {
                    return $_SESSION[$pageHash][$dataName];
                } else {
                    return "";
                }
            }
        }
        static function setByPage($dataName="",$value="") {
            if ($dataName != "") {
                $pageHash = View::getPageHash();
                if ($value=="") {
                    if (isset($_SESSION[$pageHash][$dataName])) {
                        unset($_SESSION[$pageHash][$dataName]);
                        return true;
                    }
                } else {
                    $_SESSION[$pageHash][$dataName] = $value;
                    return true;
                }
            }
            return false;
        }
        static function id() {
            return session_id();
        }
    }