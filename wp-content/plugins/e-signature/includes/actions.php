<?php

/**
 * Front-end Actions
 *
 * @package     ESIG
 * @subpackage  Functions
 * @since       1.5.1
 */
// Exit if accessed directly
if (!defined('ABSPATH'))
    exit;

/**
 * Hooks Esig actions, when present in the $_GET superglobal. Every esig_action
 * present in $_GET is called using WordPress's do_action function. These
 * functions are called on init.
 *
 * @since 1.5.1
 * @return void
 */
function esig_run_get_actions() {
    $esig_action = esigget('esig_action');
    if ($esig_action) {
        do_action('esig_' . $esig_action);
    }
}

add_action('init', 'esig_run_get_actions');

/**
 * Hooks esig actions, when present in the $_POST superglobal. Every esig_action
 * present in $_POST is called using WordPress's do_action function. These
 * functions are called on init.
 *
 * @since 1.5.1
 * @return void
 */

function esig_run_post_actions() {
    $esig_action = esigpost('esig_action');
    if ($esig_action) {
        do_action('esig_' . $esig_action);
    }
}

add_action('init', 'esig_run_post_actions');
