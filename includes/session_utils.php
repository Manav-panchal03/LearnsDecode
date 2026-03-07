<?php
// utility functions for managing role-based session namespaces

/**
 * Activate a stored role session, copying its values into the generic globals.
 * Returns true if the role existed and was activated, false otherwise.
 */
function activateRole($role) {
    if(isset($_SESSION[$role]) && is_array($_SESSION[$role])){
        $data = $_SESSION[$role];
        // copy values into the common keys
        $_SESSION['user_id']      = $data['user_id'];
        $_SESSION['user_name']    = $data['user_name'];
        $_SESSION['user_email']   = $data['user_email'];
        $_SESSION['user_role']    = $data['user_role'];
        $_SESSION['user_approved']= $data['user_approved'];
        $_SESSION['login_time']   = $data['login_time'];
        // ensure role is listed
        if(!isset($_SESSION['roles']) || !is_array($_SESSION['roles'])){
            $_SESSION['roles'] = [];
        }
        if(!in_array($role, $_SESSION['roles'])){
            $_SESSION['roles'][] = $role;
        }
        return true;
    }
    return false;
}

/**
 * Convenience check: returns true if a role has an active namespace stored.
 */
function hasRole($role) {
    return isset($_SESSION[$role]) && is_array($_SESSION[$role]);
}
