<?php

/**
 * @package bones
 * @author John Robinson <john@bayfrontmedia.com>
 * @link https://www.bayfrontmedia.com
 */

/*
 * Already defined by the app:
 *
 * APP_ROOT_PATH
 * APP_PUBLIC_PATH
 */

/**
 * Paths without trailing slashes
 *
 * Because APP_ROOT_PATH is defined (App::start() would have thrown an exception),
 * undefined constant inspection can be disabled here.
 *
 * @noinspection PhpUndefinedConstantInspection
 */

// App

const APP_CONFIG_PATH = APP_ROOT_PATH . '/config'; // Path to the application's `config` directory
const APP_RESOURCES_PATH = APP_ROOT_PATH . '/resources'; // Path to the application's `resources` directory
const APP_STORAGE_PATH = APP_ROOT_PATH . '/storage'; // Path to the application's `storage` directory

// Bones

define('BONES_ROOT_PATH', dirname(__FILE__, 2)); // Root path to the Bones directory
const BONES_RESOURCES_PATH = BONES_ROOT_PATH . '/resources'; // Path to the Bones `resources` directory
const BONES_VERSION = '1.4.1'; // Current Bones version