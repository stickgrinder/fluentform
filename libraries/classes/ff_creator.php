<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class FF_Creator
{

  private $_renderer; // object that will render the form, to be set by set_renderer() method
  private $_validator; // object that will validate the form, to be set by set_validator() method

  private $_form_structure; // form descriptor

  private $_config; // configuration options (from config file, "creator" key)

  private $_last_group; // hold last group and field information; useful to implement fluent interface
  private $_last_field; // NOTE: you could explicitely close last opened group using close_group() method
  private $_unique_ids_registry;

  // Magic Methods
  public function __construct( $config = array() )
  {
    $this->_form_structure = array(
      'properties' => array(),
      'items' => array(),
    );

    $this->_config = $config;

    $this->make_multipart(FALSE);
    $this->set_action('');
    $this->set_attributes(array());
    $this->set_max_file_size(0); // 0 for no limit

    $this->_last_group = FALSE;
    $this->_last_field = FALSE;
    $this->_unique_ids_registry = array();
  }

  public function __toString()
  {

    return $this->render_form();

  }

  // Dependency injection methods
  public function set_renderer( FF_renderer $_renderer )
  {
    $this->_renderer = $_renderer;
    return $this;
  }

  public function get_renderer()
  {
    return $this->_renderer;
  }


  public function set_validator( FF_validator $_validator )
  {
    $this->_validator = $_validator;
    return $this;
  }

  public function get_validator()
  {
    return $this->_validator;
  }


  public function get_form_structure()
  {
    return (array)$this->_form_structure;
  }

  public function get_groups() {
    $groups = array();
    $items = $this->_form_structure['items'];

    foreach ($items as $item_name => $item) {
      if ($item['type'] === 'group') {
        $groups[$item_name] = $item;
      }
    }

    return $groups;
  }

  public function set_form_structure( array $form_structure )
  {
    if (is_array($form_structure) && ! empty($form_structure)) {
      $this->_form_structure = $form_structure;
    }

    return $this;
  }

  public function dump_form_structure()
  {
    return var_export($this->get_form_structure());
  }

  public function load_form_structure( string $form_structure )
  {
    return eval('$this->set_form_structure('.(string)$form_structure.');');
  }


  // Rendering functions proxies
  public function render_raw_field( $field_name = FALSE )
  {

    if (!$field_name) return FALSE;

    // is renderer already set and is it capable of getting job done?
    if (is_object($this->_renderer))
    {

      // tell it about our form and ask for some crude tags
      $this->_renderer->set_form_structure($this->_form_structure);
      return $this->_renderer->render_field($field_name, FALSE);
    }

    // if renderer is not set, nothing to do
    return FALSE;
  }

  public function render_field( $field_name = FALSE )
  {

    if (!$field_name) return FALSE;

    // is renderer already set and is it capable of getting job done?
    if (is_object($this->_renderer))
    {
      // tell it about our form and ask for some cooked tags
      $this->_renderer->set_form_structure($this->_form_structure);
      return $this->_renderer->render_field($field_name);
    }

    // if renderer is not set, nothing to do
    return FALSE;
  }

  public function render_group( $group_name = FALSE )
  {

    if (!$group_name) return FALSE;

    // is renderer already set and is it capable of getting job done?
    if (is_object($this->_renderer))
    {

      // tell it about our form and ask for some lovely tags
      $this->_renderer->set_form_structure($this->_form_structure);
      return $this->_renderer->render_group($group_name);

    }

    // if renderer is not set, nothing to do
    return FALSE;
  }

  public function render_form_tag()
  {

    // is renderer already set and is it capable of getting job done?
    if (is_object($this->_renderer))
    {

      // tell it about our form and ask for some lovely tags
      $this->_renderer->set_form_structure($this->_form_structure);
      return $this->_renderer->render_form_tag();

    }

    // if renderer is not set, nothing to do
    return FALSE;
  }

  public function render_form_close()
  {

    // is renderer already set and is it capable of getting job done?
    if (is_object($this->_renderer))
    {

      // tell it about our form and ask for some lovely tags
      $this->_renderer->set_form_structure($this->_form_structure);
      return $this->_renderer->render_form_close();

    }

    // if renderer is not set, nothing to do
    return FALSE;
  }

  public function render_form()
  {

    // is renderer already set and is it capable of getting job done?
    if (is_object($this->_renderer))
    {

      // tell it about our form and ask for some lovely tags
      $this->_renderer->set_form_structure($this->_form_structure);
      return $this->_renderer->render_form();

    }

    // if renderer is not set, nothing to do
    return FALSE;
  }

  // Validator proxy function
  public function validate()
  {

    // is validator already set and is it capable of getting job done?
    if (is_object($this->_validator))
    {
      // tell it about our form and make it rock
      $this->_validator->set_form_structure($this->_form_structure);
      return $this->_validator->run();
    }

    // if validator is not set, nothing to do
    return FALSE;

  }

  private function has_buttons ()
  {

    // if form structure is not an array, we're done
    if (! is_array( $this->_form_structure ) || ! is_array( $this->_form_structure['items'] ) )
      return FALSE;

    $result = FALSE;

    // else iterate on form structure and set true if a button (submit or whatever) is found
    foreach ( $this->_form_structure['items'] as $name => $item ) {

      // if this item is a fieldset, dive in!
      if ( is_set($item['items']) && is_array($item['items']) )
        if ( $this->has_buttons($item) ) return TRUE;

      // if not, test if it is kinda button
      if ( in_array($item['type'], array('submit', 'button', 'reset')) ) return TRUE;

    }

    // nothing found
    return FALSE;

  }

  // Form construction set
  public function make_multipart($is_multipart = TRUE)
  {
    $this->_form_structure['properties']['is_multipart'] = (bool)$is_multipart;

    return $this;
  }

  public function set_action ( $action = NULL )
  {
    $this->_form_structure['properties']['action'] = $action;

    return $this;
  }

  public function set_attributes ( $attributes = NULL )
  {
    $this->_form_structure['properties']['attributes'] = $this->_process_attributes($attributes, array('fluentform', 'form'));

    return $this;
  }

  public function set_max_file_size( $size = FALSE )
  {
    if ($size && is_integer($size))
      $this->_form_structure['properties']['max_file_size'] = $size;

    return $this;
  }

  public function set_properties ( $action = NULL, $is_multipart = FALSE, $attributes = NULL )
  {
    $this->set_action($action);
    $this->make_multipart($is_multipart);
    $this->set_attributes($attributes);

    return $this;
  }

  public function add_group($name = '', $legend = '', $attributes = array())
  {
    if (!empty($name)) {

      // allow a group and a field to have same name, since they are different in nature
      $name .= '_group';

      // add a key to the form
      $this->_form_structure['items'][$name] = array(
        'type' => 'group',
        'name' => $name,
        'legend' => $legend,
        'attributes' => $this->_process_attributes($attributes, array('field-group')),
        'items' => array(),
      );

      // set current group so that fields will be added to this one until
      // a new one is created or group_close() is called.
      $this->_last_group = $name;

    }

    return $this;
  }

  public function close_group ( )
  {
    // set current group to FALSE so fields are automatically attached to form container.
    $this->_last_group = FALSE;

    return $this;
  }

  // Generic field setter (it always knows it has to set _last_field)
  private function _add_field($field)
  {

    if (!isset($field['name']) || empty($field['name'])) return $this;

    $name = $field['name'];
    $attributes = isset($field['attributes']) ? $field['attributes'] : array();

    // create a unique if if none is defined
    if (!isset($attributes['id']) || empty($attributes['id']) || trim($attributes['id']) == '')
    {
      // normalize field id getting rid of strange characters in name
      $field_id = preg_replace('/_+/', '_', preg_replace('/\[([^\[\]]*)\]/', '_$1_', $name));

      if ( ! isset($this->_unique_ids_registry[$field_id]) )
        $this->_unique_ids_registry[$field_id] = 0;
      else
        $this->_unique_ids_registry[$field_id]++;

      if ($this->_config['auto_id_integer_append'])
        $attributes['id'] = $field_id . $this->_unique_ids_registry[$field_id];
      else
        $attributes['id'] =  preg_replace('/_$/', '', $field_id);
    }

    // assign new attributes back to the field
    $field['attributes'] = $attributes;
    unset($attributes);


    // if we have an open group, let's add field to that
    if ( $this->_last_group != FALSE && !empty($this->_last_group) )
    {
      $this->_form_structure['items'][$this->_last_group]['items'][$name] = $field;
    }
    else
    {
      $this->_form_structure['items'][$name] = $field;
    }

    $this->_last_field = $name; // actually, we don't need this... :/ mmmh

    return $this;
  }

  // Understand how attributes have been passed and reconstruct them a better way
  private function _process_attributes($attributes, $additional_classes = FALSE)
  {

    // if attribute is a string, explode it into an array, so that we could manage them
    // more easily

    if ( is_string($attributes) )
    {
      // let PHP do the hard work breaking down attributes one by one :)
      $xml = simplexml_load_string( '<dummy ' . $attributes . ' />' );
      $attributes = array();
      foreach ($xml->attributes() as $name=>$value)
      {
        // if attribute is not empty, store it
        $value = (array)$value;
        if (isset($value[0]) && !empty($value[0]))
          $attributes[$name] = $value[0];
      }
    }

    // if attribute is an array already, go on but remember to normalize attribute case
    $attributes = array_change_key_case((array)$attributes, CASE_LOWER);

    // maybe no class has been given, so class key is not set. Let's set it
    // and...
    if (!isset($attributes['class'])) {
      $attributes['class'] = '';
    }

    // ... merge user classes and FB classes
    if ( isset($additional_classes) && is_array($additional_classes) )
    {
      // put order in the mess
      $attributes['class'] = preg_replace( "/\s+/", " ", $attributes['class'] );
      // break it apart
      $attributes['class'] = explode(" ", $attributes['class']);
      // merge the arrays, getting rid of duplicate entries
      $attributes['class'] = array_unique ( array_merge( $additional_classes, $attributes['class'] ) );
      // and now, reglue all pieces together
      $attributes['class'] = implode( " ", $attributes['class'] );
    }

    return $attributes;

  }

  // Basic field types definition
  public function add_hidden_field ( $name = '', $value = '' )
  {
    return $this->_add_field( array(
      'type' => 'hidden',
      'name' => $name,
      'value' => $value
    ));
  }

  public function add_text_field ( $name = '', $label = '', $value = '', $attributes=array(), $rules='' )
  {
    return $this->_add_field( array(
	  //allow input type to be set, default to type="text"
      'type' => isset($attributes['type']) ? $attributes['type'] : 'text',
      'name' => $name,
      'label' => $label,
      'value' => $value,
      'attributes' => $this->_process_attributes($attributes, array('text', 'field')),
      'rules' => $rules,
    ));
  }

  public function add_file_field ( $name = '', $label = '', $value = '', $attributes=array(), $rules='' )
  {
    // form must be multipart, to submit a file
    $this->make_multipart();

    return $this->_add_field( array(
      'type' => 'file',
      'name' => $name,
      'label' => $label,
      'value' => $value,
      'attributes' => $this->_process_attributes($attributes, array('file', 'field')),
      'rules' => $rules,
    ));
  }

  public function add_text_area ( $name = '', $label = '', $value = '', $attributes=array(), $rules='' )
  {
    return $this->_add_field( array(
      'type' => 'textarea',
      'name' => $name,
      'label' => $label,
      'value' => $value,
      'attributes' => $this->_process_attributes($attributes, array('textarea', 'field')),
      'rules' => $rules,
    ));
  }

  public function add_password_field ( $name = '', $label = '', $value = '', $attributes=array(), $rules='' )
  {
    return $this->_add_field( array(
      'type' => 'password',
      'name' => $name,
      'label' => $label,
      'value' => $value,
      'attributes' => $this->_process_attributes($attributes, array('password', 'field')),
      'rules' => $rules,
    ));
  }

  public function add_dropdown_field ( $name = '', $label = '', $options = array(), $value = '', $attributes=array(), $rules=''  )
  {

    // if multiple options are already selected, we need a multiselect
    if ( is_array($value) )
      return $this->add_multiselect_field( $name = '', $label = '', $options = array(), $value = '', $attributes=array(), $rules='' );

    return $this->_add_field( array(
      'type' => 'dropdown',
      'name' => $name,
      'label' => $label,
      'options' => $options,
      'value' => $value,
      'attributes' => $this->_process_attributes($attributes, array('dropdown', 'field')),
      'rules' => $rules,
    ));

  }

  public function add_multiselect_field ( $name = '', $label = '', $options = array(), $value = '', $attributes=array(), $rules='' )
  {

    // In case of multiselect, an array must be sent by the form. Search for [] symbols
    // or add them
    if (! preg_match('/\[\s*\]$/', $name) )
      $name .= '[]';

    return $this->_add_field( array(
      'type' => 'multiselect',
      'name' => $name,
      'label' => $label,
      'options' => $options,
      'value' => $value,
      'attributes' => $this->_process_attributes($attributes, array('multiselect', 'field')),
      'rules' => $rules,
    ));

  }

  public function add_checkbox ( $name = '', $label = '', $label_position = FF_R_CRB_LABEL_AFTER, $value = '', $checked = FALSE, $attributes=array(), $rules='' )
  {
    return $this->_add_field( array(
      'type' => 'checkbox',
      'name' => $name,
      'label' => $label,
      'label_position' => $label_position,  // 0 => afterwords, 1 => before
      'value' => $value,
      'checked' => $checked,
      'attributes' => $this->_process_attributes($attributes, array('checkbox', 'field')),
      'rules' => $rules,
    ));
  }

  public function add_checkboxes ( $name = '', $label = '', $label_position = FF_R_CRB_LABEL_AFTER, $options = array(), $attributes=array(), $rules='' )
  {

    // In case of multiselect, an array must be sent by the form. Search for [] symbols
    // or add them
    if (! preg_match('/\[\s*\]$/', $name) )
      $name .= '[]';

    return $this->_add_field( array(
      'type' => 'checkboxes',
      'name' => $name,
      'label' => $label,
      'label_position' => $label_position,
      'options' => $options,
      'attributes' => $this->_process_attributes($attributes, array('checkboxes', 'field')),
      'rules' => $rules,
    ));

  }

  public function add_radiobutton ( $name = '', $label = '', $label_position = FF_R_CRB_LABEL_AFTER, $value = '', $selected = FALSE, $attributes=array(), $rules='' )
  {
    return $this->_add_field( array(
      'type' => 'radiobutton',
      'name' => $name,
      'label' => $label,
      'label_position' => $label_position,
      'value' => $value,
      'selected' => $selected,
      'attributes' => $this->_process_attributes($attributes, array('radiobutton', 'field')),
      'rules' => $rules,
    ));
  }

  public function add_radiobuttons ( $name = '', $label = '', $label_position = FF_R_CRB_LABEL_AFTER, $options = array(), $value = '', $attributes=array(), $rules='' )
  {

    // In case of multiselect, an array must be sent by the form. Search for [] symbols
    // or add them
    if (! preg_match('/\[\s*\]$/', $name) )
      $name .= '[]';

    return $this->_add_field( array(
      'type' => 'radiobuttons',
      'name' => $name,
      'label' => $label,
      'label_position' => $label_position,
      'options' => $options,
      'value' => $value,
      'attributes' => $this->_process_attributes($attributes, array('radiobuttons', 'field')),
      'rules' => $rules,
    ));

  }

  public function add_button ( $name = '', $value = '', $type = 'button', $attributes = array() )
  {
    return $this->_add_field( array(
      'type' => $type,
      'name' => $name,
      'value' => $value,
      'attributes' => $this->_process_attributes($attributes, array($type, 'button')),
    ));
  }

  // Advanced field types definition

  public function add_submit ( $name = '', $value = '', $attributes = array() )
  {
    return $this->add_button ( $name, $value, 'submit', $attributes );
  }

  public function add_reset ( $name = '', $value = '', $attributes = array() )
  {
    return $this->add_button ( $name, $value, 'reset', $attributes );
  }

  public function add_email_field ( $name = '', $label = '', $value = '', $attributes = array(), $required = FALSE )
  {
    // add email class and set type="email" if type not explicitly overriden by user
    $attributes = array_merge( array( 'type' => 'email' ), $this->_process_attributes($attributes, array( 'email' )) );
    // set optimal rules for email validation
    $rules = 'trim|' . ($required ? 'required|' : '') . 'valid_email|xss_clean';

    return $this->add_text_field( $name, $label, $value, $attributes, $rules );

  }

  public function add_password_block ( $name = '', $label = '', $conf_label = '', $attributes = array() )
  {

    // add confirmation class
    $conf_attributes = $this->_process_attributes($attributes, array( 'confirmation' ));
    // set optimal rules for email validation
    $rules = 'trim|required|min_length[6]';
    $conf_rules = 'trim|required|matches['.$name.']';

    $this->add_password_field( $name, $label, $attributes, $rules );
    return $this->add_password_field( $name.'_conf', $conf_label, $conf_attributes, $conf_rules );

  }

  public function add_numeric_field ( $name = '', $label = '', $value = '', $attributes = array(), $required = FALSE )
  {
    // add numeric class and set type="number" if type not explicitly overridden by user
    $attributes = array_merge(array( 'type' => 'number' ) , $this->_process_attributes($attributes, array( 'numeric' )) );
    // set optimal rules for email validation
    $rules = 'trim|' . ($required ? 'required|' : '') . 'numeric';

    return $this->add_text_field( $name, $label, $value, $attributes, $rules );

  }

  public function add_integer_field ( $name = '', $label = '', $value = '', $attributes=array(), $required = FALSE )
  {
    // add integer class and set type="number" if type not explicitly overridden by user
    $attributes = array_merge(array( 'type' => 'number' ) , $this->_process_attributes($attributes, array( 'integer' )) );
    // set optimal rules for email validation
    $rules = 'trim|' . ($required ? 'required|' : '') . 'integer';

    return $this->add_text_field( $name, $label, $value, $attributes, $rules );

  }

}
// --------------------------------------------------------------------
/**
 * End of FF_creator
**/
