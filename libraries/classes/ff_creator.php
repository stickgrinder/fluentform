<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class FF_Creator
{

  private $_renderer; // object that will render the form, to be set by set_renderer() method
  private $_validator; // object that will validate the form, to be set by set_validator() method

  private $_form_structure; // form descriptor

  private $_config; // configuration options (from config file, "creator" key)

  private $_last_fieldset; // hold last fieldset and field information; useful to implement fluent interface
  private $_last_field; // NOTE: you could explicitly close last opened fieldset using close_fieldset() method
  private $_unique_ids_registry;

  // -------------------------------------------------------------
  // Magic Methods
  // -------------------------------------------------------------
	
  /**
   * Constructor. Initializes some defaults for form structure.
   * 
   * @param array $config
   */
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

    $this->_last_fieldset = FALSE;
    $this->_last_field = FALSE;
    $this->_unique_ids_registry = array();
  }

  /**
   * __toString() renders the form.
   * 
   * @see FF_Creator::render_form()
   * @return false|string
   */
  public function __toString()
  {

    return $this->render_form();

  }

  // -------------------------------------------------------------
  // Dependency injection methods
  // -------------------------------------------------------------

  /**
   * Set (compose) the renderer object
   * 
   * @param FF_Renderer $_renderer
   * @return FF_Creator
   */
  public function set_renderer( FF_Renderer $_renderer )
  {
    $this->_renderer = $_renderer;
    return $this;
  }

  /**
   * Get the FluentForm renderer object.
   * 
   * @return FF_Renderer
   */
  public function get_renderer()
  {
    return $this->_renderer;
  }

  /**
   * Set (compose) the FluentForm form validation object.  
   * 
   * @param FF_validator $_validator
   * @return FF_Creator
   */
  public function set_validator( FF_validator $_validator )
  {
    $this->_validator = $_validator;
    return $this;
  }

  /**
   * Return the FluentForm form validation object
   * 
   * @return FF_Validator 
   */
  public function get_validator()
  {
    return $this->_validator;
  }

  /**
   * Return the entire form structure as an array.
   * 
   * @return array
   */
  public function get_form_structure()
  {
    return (array)$this->_form_structure;
  }

  /**
   * Return an array of the field fieldsets
   * 
   * @return array
   */
  public function get_fieldsets() 
  {
    $fieldsets = array();
    $items = $this->_form_structure['items'];

    foreach ($items as $item_name => $item) 
    {
      if ($item['type'] === 'fieldset') 
      {
        $fieldsets[$item_name] = $item;
      }
    }

    return $fieldsets;
  }

  /**
   * Set the form structure from supplied array.
   * 
   * @param array $form_structure
   * @return FF_Creator
   */
  public function set_form_structure( array $form_structure )
  {
    if (is_array($form_structure) && ! empty($form_structure)) {
      $this->_form_structure = $form_structure;
    }

    return $this;
  }

  /**
   * Return the form structure as a string, i.e. var_dump() it.
   * 
   * @return string
   */
  public function dump_form_structure()
  {
    return var_export($this->get_form_structure());
  }

  /**
   * Load the form structure from supplied string.
   * 
   * @param string $form_structure
   * @return FF_Creator
   */
  public function load_form_structure( string $form_structure )
  {
    return eval('$this->set_form_structure('.(string)$form_structure.');');
  }

  // -------------------------------------------------------------
  // Rendering functions proxies
  // -------------------------------------------------------------
  	
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

  /**
   * Render a single form field.
   * Returns false if renderer is not set.
   * 
   * @param bool $field_name
   * @return string|false
   */
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

  /**
   * Render a single fieldset <fieldset>...</fieldset>
   * Returns false if renderer is not set.
   * 
   * @param bool $fieldset_name
   * @return string|false
   */
  public function render_fieldset( $fieldset_name = FALSE )
  {

    if (!$fieldset_name) return FALSE;

    // is renderer already set and is it capable of getting job done?
    if (is_object($this->_renderer))
    {

      // tell it about our form and ask for some lovely tags
      $this->_renderer->set_form_structure($this->_form_structure);
      return $this->_renderer->render_fieldset($fieldset_name);

    }

    // if renderer is not set, nothing to do
    return FALSE;
  }

  /**
   * Render the main form element <form ...>
   * Returns false if renderer is not set
   * 
   * @return string|false
   */
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

  /**
   * Close the form. </form>
   * Returns false if renderer is not set.
   * 
   * @return string|false
   */
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

  /**
   * Return the form html as a string.
   * Returns false if form renderer is not set.
   * 
   * @return string|false
   */
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

  /**
   * Validator proxy function. i.e. Runs the form validation.
   * 
   * @return bool
   */
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

  /**
   * Check if we have any buttons for the form structure.
   * 
   * @return bool
   */
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

  // -------------------------------------------------------------
  // Form construction set
  // -------------------------------------------------------------

  /**
   * Set form element enctype property. 
   * 
   * @param bool $is_multipart If true, enctype="multipart/form-data", else property omitted.
   * @return FF_Creator
   */
  public function make_multipart($is_multipart = TRUE)
  {
    $this->_form_structure['properties']['is_multipart'] = (bool)$is_multipart;

    return $this;
  }

  /**
   * Set the action property of the form html element.
   * 
   * @param null $action
   * @return FF_Creator
   * @see FF_Creator::set_properties()
   */
  public function set_action ( $action = NULL )
  {
    $this->_form_structure['properties']['action'] = $action;

    return $this;
  }

  /**
   * Set form attributes, i.e. properties of the form html element.
   * 
   * @param null|array $attributes
   * @return FF_Creator
   * @see FF_Creator::set_properties()
   */
  public function set_attributes ( $attributes = NULL )
  {
    $this->_form_structure['properties']['attributes'] = $this->_process_attributes($attributes, array('fluentform', 'form'));

    return $this;
  }

  /**
   * Set the max_file_size form attribute.
   * 
   * @param int $size
   * @return FF_Creator
   */
  public function set_max_file_size( $size = 0 )
  {
    if ($size && is_integer($size))
      $this->_form_structure['properties']['max_file_size'] = $size;

    return $this;
  }

  /**
   * Set form properties.
   * Shorthand method, equivalent to:
   *    $form->set_action($action)
   *         ->make_multipart($is_multipart)
   *         ->set_attributes($attributes);
   * 
   * @param null|string $action
   * @param bool $is_multipart
   * @param null|array|string $attributes
   * @return FF_Creator
   */
  public function set_properties ( $action = NULL, $is_multipart = FALSE, $attributes = NULL )
  {
    $this->set_action($action);
    $this->make_multipart($is_multipart);
    $this->set_attributes($attributes);

    return $this;
  }

  /**
   * Begin a fieldset <fieldset ...><legend>...</legend>
   * 
   * @param string $name
   * @param string $legend
   * @param array $attributes
   * @return FF_Creator
   */
  public function add_fieldset($name = '', $legend = '', $attributes = array())
  {
    if (!empty($name)) {

      // allow a fieldset and a field to have same name, since they are different in nature
      $name .= '_fieldset';

      // add a key to the form
      $this->_form_structure['items'][$name] = array(
        'type' => 'fieldset',
        'name' => $name,
        'legend' => $legend,
        'attributes' => $this->_process_attributes($attributes, array('fieldset')),
        'items' => array(),
      );

      // set current fieldset so that fields will be added to this one until
      // a new one is created or fieldset_close() is called.
      $this->_last_fieldset = $name;

    }

    return $this;
  }

  /**
   * Close a fieldset </fieldset>
   * 
   * @return FF_Creator
   */
  public function close_fieldset ( )
  {
    // set current fieldset to FALSE so fields are automatically attached to form container.
    $this->_last_fieldset = FALSE;

    return $this;
  }

  /**
   * Generic field setter (it always knows it has to set _last_field)
   * 
   * @param $field
   * @return FF_Creator
   */
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


    // if we have an open fieldset, let's add field to that
    if ( $this->_last_fieldset != FALSE && !empty($this->_last_fieldset) )
    {
      $this->_form_structure['items'][$this->_last_fieldset]['items'][$name] = $field;
    }
    else
    {
      $this->_form_structure['items'][$name] = $field;
    }

    $this->_last_field = $name; // actually, we don't need this... :/ mmmh

    return $this;
  }

  /**
   * Understand how attributes have been passed and reconstruct them a better way
   * 
   * @param $attributes
   * @param bool $additional_classes
   * @return array
   */
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

  
  // -------------------------------------------------------------
  // Basic field types definition
  // -------------------------------------------------------------
  
  	
  /**
   * Add a hidden field.  <input type="hidden" ...>
   * 
   * @param string $name
   * @param string $value
   * @return FF_Creator
   */
  public function add_hidden_field ( $name = '', $value = '' )
  {
    return $this->_add_field( array(
      'type' => 'hidden',
      'name' => $name,
      'value' => $value
    ));
  }

  /**
   * Add a simple text field. <input type="text" ...>
   * 
   * @param string $name
   * @param string $label
   * @param string $value
   * @param array $attributes
   * @param string $rules
   * @return FF_Creator
   */
  public function add_text_field ( $name = '', $label = '', $rules='', $value = '', $attributes=array())
  {
	$attributes = $this->_process_attributes($attributes, array('text', 'field'));
	$type = 'text';
	if(isset($attributes['type']) AND in_array($attributes['type'],array('text','email','url','number')))
	{
	  $type = $attributes['type'];
	  unset($attributes['type']);
	}	
	
    return $this->_add_field( array(
	  //allow input type to be set, default to type="text"
      'type' => $type,
      'name' => $name,
      'label' => $label,
      'value' => $value,
      'attributes' => $attributes,
      'rules' => $rules,
    ));
  }

  /**
   * Add a single file upload field. <input type="file" ...>
   * 
   * @param string $name
   * @param string $label
   * @param string $value
   * @param array $attributes
   * @param string $rules
   * @return FF_Creator
   */
  public function add_file_field ( $name = '', $label = '', $rules='', $value = '', $attributes=array())
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

  /**
   * Add a textarea field i.e. <textarea...></textarea>
   * 
   * @param string $name
   * @param string $label
   * @param string $value
   * @param array $attributes
   * @param string $rules
   * @return FF_Creator
   */
  public function add_text_area ( $name = '', $label = '', $rules='', $value = '', $attributes=array() )
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

  /**
   * Add a password field, i.e. <input type="password" ...>
   * 
   * @param string $name
   * @param string $label
   * @param string $value
   * @param array $attributes
   * @param string $required
   * @return FF_Creator
   */
  public function add_password_field ( $name = '', $label = '', $rules='', $value = '', $attributes = array() )
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

  /**
   * Add a regular dropdown. i.e. <input type="select" ...>
   * 
   * @param string $name
   * @param string $label
   * @param array $options
   * @param string $value
   * @param array $attributes
   * @param string $rules
   * @return FF_Creator
   */
  public function add_dropdown_field ( $name = '', $label = '', $options = array(), $rules='', $value = '', $attributes=array()  )
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

  /**
   * Add a multiselect dropdown
   * 
   * @param string $name
   * @param string $label
   * @param array $options
   * @param string $value
   * @param array $attributes
   * @param string $rules
   * @return FF_Creator
   */
  public function add_multiselect_field ( $name = '', $label = '', $options = array(), $rules='', $value = '', $attributes=array() )
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

  /**
   * Add a single checkbox.
   * 
   * @param string $name
   * @param string $label
   * @param int $label_position
   * @param string $value
   * @param bool $checked
   * @param array $attributes
   * @param string $rules
   * @return FF_Creator
   */
  public function add_checkbox ( $name = '', $label = '', $value = '', $rules='', $checked = FALSE, $attributes=array(), $label_position = NULL )
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

  /**
   * Add a group of checkboxes.
   * 
   * @param string $name
   * @param string $label
   * @param int $label_position
   * @param array $options
   * @param array $attributes
   * @param string $rules
   * @return FF_Creator
   */
  public function add_checkboxes ( $name = '', $label = '', $options = array(), $rules='', $attributes=array(), $label_position = NULL )
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

  /**
   * Add a single radio button.
   * 
   * @param string $name
   * @param string $label
   * @param int $label_position
   * @param string $value
   * @param bool $selected
   * @param array $attributes
   * @param string $rules
   * @return FF_Creator
   */
  public function add_radiobutton ( $name = '', $label = '', $value = '', $rules='', $checked = FALSE, $attributes=array(), $label_position = NULL )
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

  /**
   * Add a radio button group.
   * 
   * @param string $name
   * @param string $label
   * @param int $label_position
   * @param array $options
   * @param string $value
   * @param array $attributes
   * @param string $rules
   * @return FF_Creator
   */
  public function add_radiobuttons ( $name = '', $label = '', $options = array(), $rules='', $attributes=array(), $label_position = NULL )
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

  /**
   * Add a button element with type=$type and "button" added to class value. 
   * 
   * @param string $name
   * @param string $value
   * @param string $type
   * @param array $attributes
   * @return FF_Creator
   */
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

  /**
   * Add a submit button.
   * 
   * @param string $name
   * @param string $value
   * @param array $attributes
   * @return FF_Creator
   */
  public function add_submit ( $name = '', $value = '', $attributes = array() )
  {
    return $this->add_button ( $name, $value, 'submit', $attributes );
  }

  /**
   * @param string $name
   * @param string $value
   * @param array $attributes
   * @return FF_Creator
   */
  public function add_reset ( $name = '', $value = '', $attributes = array() )
  {
    return $this->add_button ( $name, $value, 'reset', $attributes );
  }


  /**
   * Add a password block, i.e. two fields: password + confirm password.
   * Auto-validation supplied: required|min_length[6] and the two values must match. 
   * 
   * @param string $name
   * @param string $label
   * @param string $conf_label
   * @param array $attributes
   * @return FF_Creator
   */
  public function add_password_block ( $name = '', $label = '', $conf_label = '', $min_length=6, $attributes = array() )
  {

    // add confirmation class
    $conf_attributes = $this->_process_attributes($attributes, array( 'confirmation' ));
    // set optimal rules for email validation
    $rules = 'trim|required|min_length['. (is_numeric($min_length) ? (integer)$min_length : 6 ).']';
    $conf_rules = 'trim|required|matches['.$name.']';

    $this->add_password_field( $name, $label, $rules, $attributes );
    return $this->add_password_field( $name.'_conf', $conf_label, $conf_rules, $conf_attributes );

  }

  /**
   * Add text input with type="email" and "email" added to class value.
   * Last argument $required can be a boolean for automatic rules, 
   * or a Form_Validation rule string may be supplied. 
   * 
   * @param string $name
   * @param string $label
   * @param string $value
   * @param array $attributes
   * @param bool $required
   * @return FF_Creator
   */
  public function add_email_field ( $name = '', $label = '', $required = FALSE, $value = '', $attributes = array() )
  {
    // add email class and set type = "email"
    $attributes = array_merge( $this->_process_attributes($attributes, array( 'email' )), array( 'type' => 'email' ) );

    // by default set optimal rules for email validation, or allow manual rules
    $rules = is_bool($required) ? 'trim|' . ($required ? 'required|' : '') . 'valid_email|xss_clean' : $required;
	
    return $this->add_text_field( $name, $label, $rules, $value, $attributes );
  }

  /**
   * Add text input with type="url" and "url" added to class value.
   * Last argument $required can be a boolean for automatic rules, 
   * or a Form_Validation rule string may be supplied.
   * 
   * @param string $name
   * @param string $label
   * @param string $value
   * @param array $attributes
   * @param bool $required
   * @return FF_Creator
   */
  public function add_url_field ( $name = '', $label = '', $required = FALSE, $value = '', $attributes = array() )
  {
    // add url class and set type = "url"
    $attributes = array_merge( $this->_process_attributes($attributes, array( 'url' )), array( 'type' => 'url' ) );
	
    // set optimal rules for url validation, or allow manual rules
    $rules = is_bool($required) ? 'trim|' . ($required ? 'required|' : '') . 'valid_url|prep_url|xss_clean' 
		                        : $required;

    return $this->add_text_field( $name, $label, $rules, $value, $attributes );
  }
	
  /**
   * Add a text input with type="number" and "numeric" added to supplied class value.
   * Last argument $required can be a boolean for automatic rules, 
   * or a Form_Validation rule string may be supplied.  
   * 
   * @param string $name
   * @param string $label
   * @param string $value
   * @param array $attributes
   * @param bool $required
   * @return FF_Creator
   */
  public function add_numeric_field ( $name = '', $label = '', $required = FALSE, $value = '', $attributes = array() )
  {
    // add numeric class and set type="number" 
    $attributes = array_merge( $this->_process_attributes($attributes, array( 'numeric' )), array( 'type' => 'number' ) );
	  
    // by default set optimal rules for numeric validation, or use manual rule string
    $rules = is_bool($required) ? 'trim|' . ($required ? 'required|' : '') . 'numeric' : $required;

    return $this->add_text_field( $name, $label, $rules, $value, $attributes );
  }

  /**
   * Add a text input with type="number" and "integer" added to supplied class value.
   * Last argument $required can be a boolean for automatic rules, 
   * or a Form_Validation rule string may be supplied.  
   * 
   * @param string $name
   * @param string $label
   * @param string $value
   * @param array $attributes
   * @param bool $required
   * @return FF_Creator
   */
  public function add_integer_field ( $name = '', $label = '', $required = FALSE, $value = '', $attributes=array() )
  {
    // add integer class and set type="number" if type not explicitly overridden by user
    $attributes = array_merge( $this->_process_attributes($attributes, array( 'integer' )), array( 'type' => 'number' ) );
	  
    // set optimal rules for email validation
    $rules = is_bool($required) ? 'trim|' . ($required ? 'required|' : '') . 'integer' : $required;

    return $this->add_text_field( $name, $label, $rules, $value, $attributes );

  }

}
// --------------------------------------------------------------------
/**
 * End of FF_creator
**/
