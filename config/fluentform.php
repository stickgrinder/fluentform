<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Constants definition
 */
define('FF_R_CRB_LABEL_NONE', FALSE);
define('FF_R_CRB_LABEL_BEFORE', 11);
define('FF_R_CRB_LABEL_AFTER', 21);

define('FF_R_ERROR_HIDE', -1);
define('FF_R_ERROR_SHOW_FORM', 0);
define('FF_R_ERROR_SHOW_FIELD', 1);

define('FF_R_ERRPOS_BEFOREOPEN', 3);
define('FF_R_ERRPOS_AFTEROPEN', 13);
define('FF_R_ERRPOS_BEFORECLOSE', 23);
define('FF_R_ERRPOS_AFTERCLOSE', 33);

// if FALSE, fields with the same name will have the very same ID;
// still, when adding multifields using add_checkboxes() and add_radiobuttons()
// methods, the correct naming is carried out automatically to avoid conflicts
$config['fluentform_creator']['auto_id_integer_append'] = FALSE;

$config['fluentform_validator']['error_delimiter_tag'] = 'p';
$config['fluentform_validator']['error_delimiter_classes'] = 'errors global message';

$config['fluentform_renderer']['field_wrapper_tag'] = 'div';
// if TRUE adds all field classes to wrapper also (note that auto-classes
// as "required" or "error" is automatically appended to wrapper)
$config['fluentform_renderer']['field_wrapper_add_field_classes'] = FALSE;
$config['fluentform_renderer']['field_wrapper_classes'] = 'field-wrapper';
// if id_suffix is set and if not empty string, an id will be added to the wrapper
// in the form <field_id><id_suffix>. So for a field_id "foo" and a suffix "bar"
// the result will be "foobar"
$config['fluentform_renderer']['field_wrapper_id_suffix'] = '_wrapper';
$config['fluentform_renderer']['error_show'] = FF_R_ERROR_SHOW_FIELD;
$config['fluentform_renderer']['error_position'] = FF_R_ERRPOS_BEFORECLOSE;
// this is a string, such as 'error wrong bad changeme', not an array
$config['fluentform_renderer']['error_classes'] = 'error';
// this is a string, such as 'needed mandatory required fillme', not an array
$config['fluentform_renderer']['required_classes'] = 'required';
