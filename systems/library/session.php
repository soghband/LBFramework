<?php
    class session {
        static function start() {
            ini_set("session.use_cookies", 1);
            ini_set("session.cookie_httponly", 1);
            ini_set("session.cookie_secure", 0);
            ini_set("session.use_only_cookies", 0);
            ini_set("session.use_trans_sid", 0);
            ini_set("session.cache_limiter", "");
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
            if ($dataName == "") {
                return false;
            } else {
                if ($value=="") {
                    if (isset($_SESSION[$dataName])) {
                        unset($_SESSION[$dataName]);
                        return true;
                    } else {
                        return false;
                    }
                } else {
                    $_SESSION[$dataName] = $value;
                    return true;
                }
            }
            return false;
        }
        static function getByPage($dataName = "") {
            $pageHash = view::getPageHash();
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
            if ($dataName == "") {
                return false;
            } else {
                $pageHash = view::getPageHash();
                if ($value=="") {
                    if (isset($_SESSION[$pageHash][$dataName])) {
                        unset($_SESSION[$pageHash][$dataName]);
                        return true;
                    } else {
                        return false;
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