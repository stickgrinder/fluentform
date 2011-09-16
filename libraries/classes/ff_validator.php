<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class FF_Validator extends CI_form_validation
{

  private $_form_structure;
  private $_holder;
  private $_config; // configuration options (from config file, "validator" key)


  public function __construct( $config = array(), FF_Creator $holder = NULL )
  {
    parent::__construct();

    $this->_holder = $holder;

    // set configuration
    $this->_config = $config;

    $this->set_error_delimiters(
      '<'.$this->_config['error_delimiter_tag'].' class="'.$this->_config['error_delimiter_classes'].'">',
      $this->_wrapper_close = '</'.$this->_config['error_delimiter_tag'].'>'
    ); // TODO: understand why the hell I wrote this crap! O_o

  }

  /**
   * Set the form the validation routines will run on
   *
   * Parameter is taken from the form structure
   *
   * @access  private  //TODO: shouldn't it be "public"?
   * @param array
   * @return null
   */
  public function set_form_structure(array $form_structure = array())
  {
    // set the form structure
    $this->_form_structure = $form_structure;
    // read rules and set them
    $this->_set_rules_from_items_list($this->_form_structure['items']);

    return $this;
  }

  private function _set_rules_from_items_list($items = FALSE)
  {

    // is there something in this form at all?
    if (is_array($items) and count($items)>0)
    {

      // retrieve all field names, labels and rules and setup
      // an on-the-fly configuration
      foreach ($items as $item) {

        // items could be fields or groups; let's figure this out
        if (isset($item['items']) && is_array($item['items']))
          $this->_set_rules_from_items_list($item['items']);

        else
          $this->_set_rules_for_item($item);

      }
    }

    return $this;

  }

  private function _set_rules_for_item($item = FALSE)
  {

    if (is_array($item) && count($item > 0))
    {
      
      // if no rule is set, try to fetch default ones
      if ( !isset( $item['rules'] ) || empty( $item['rules'] ) )
        if ( isset( $this->_config['default_rules'][$item['type']] ) )
          $item['rules'] = $this->_config['default_rules'][$item['type']];
      
      // if item description makes sense, set the rules for that item
      if (
        ( isset( $item['name'] ) && ! empty( $item['name'] ) ) &&
        ( isset( $item['label'] ) && ! empty( $item['label'] ) ) &&
        ( isset( $item['rules'] ) && ! empty( $item['rules'] ) )
      )
        $this->set_rules( $item['name'], $item['label'], $item['rules'] );
    }

    return $this;

  }

  /**
   * Executes the Validation routines
   *
   * This method has been extended to allow callbacks function
   * in the model: setting callback as callback_<function_name>_model[<model_name>]
   * will invoke specified model method, if available.
   * Thanks to @janogarcia (twitter) for this!
   *
   * @access  private
   * @param array
   * @param array
   * @param mixed
   * @param integer
   * @return  mixed
   */
  function _execute($row, $rules, $postdata = NULL, $cycles = 0)
  {
    // If the $_POST data is an array we will run a recursive call
    if (is_array($postdata))
    {
      foreach ($postdata as $key => $val)
      {
        $this->_execute($row, $rules, $val, $cycles);
        $cycles++;
      }

      return;
    }

    // --------------------------------------------------------------------

    // If the field is blank, but NOT required, no further tests are necessary
    $callback = FALSE;
    if ( ! in_array('required', $rules) AND is_null($postdata))
    {
      // Before we bail out, does the rule contain a callback?
      if (preg_match("/(callback_\w+)/", implode(' ', $rules), $match))
      {
        $callback = TRUE;
        $rules = (array('1' => $match[1]));
      }
      else
      {
        return;
      }
    }

    // --------------------------------------------------------------------

    // Isset Test. Typically this rule will only apply to checkboxes.
    if (is_null($postdata) AND $callback == FALSE)
    {
      if (in_array('isset', $rules, TRUE) OR in_array('required', $rules))
      {
        // Set the message type
        $type = (in_array('required', $rules)) ? 'required' : 'isset';

        if ( ! isset($this->_error_messages[$type]))
        {
          if (FALSE === ($line = $this->CI->lang->line($type)))
          {
            $line = 'The field was not set';
          }
        }
        else
        {
          $line = $this->_error_messages[$type];
        }

        // Build the error message
        $message = sprintf($line, $this->_translate_fieldname($row['label']));

        // Save the error message
        $this->_field_data[$row['field']]['error'] = $message;

        if ( ! isset($this->_error_array[$row['field']]))
        {
          $this->_error_array[$row['field']] = $message;
        }
      }

      return;
    }

    // --------------------------------------------------------------------

    // Cycle through each rule and run it
    foreach ($rules As $rule)
    {
      $_in_array = FALSE;

      // We set the $postdata variable with the current data in our master array so that
      // each cycle of the loop is dealing with the processed data from the last cycle
      if ($row['is_array'] == TRUE AND is_array($this->_field_data[$row['field']]['postdata']))
      {
        // We shouldn't need this safety, but just in case there isn't an array index
        // associated with this cycle we'll bail out
        if ( ! isset($this->_field_data[$row['field']]['postdata'][$cycles]))
        {
          continue;
        }

        $postdata = $this->_field_data[$row['field']]['postdata'][$cycles];
        $_in_array = TRUE;
      }
      else
      {
        $postdata = $this->_field_data[$row['field']]['postdata'];
      }

      // --------------------------------------------------------------------

      // Is the rule a callback?
      $callback = FALSE;
      if (substr($rule, 0, 9) == 'callback_')
      {
        $rule = substr($rule, 9);
        $callback = TRUE;
      }

      // Strip the parameter (if exists) from the rule
      // Rules can contain a parameter: max_length[5]
      $param = FALSE;
      if (preg_match("/(.*?)\[(.*?)\]/", $rule, $match))
      {
        $rule = $match[1];
        $param  = $match[2];
      }

      // Call the function that corresponds to the rule
      if ($callback === TRUE)
      {
        // >>> START of modificaction

        $model = FALSE;

        if (strpos($rule, '_model') AND $param)
        {
          $model = $param;
          $rule = substr($rule, 0, -6);
        }

        // Is the callback into a model?
        if ($model)
        {
          if ( ! method_exists($this->CI->$model, $rule))
          {
            continue;
          }

          // Run the function and grab the result
          $result = $this->CI->$model->$rule($postdata);
        }
        else
        {
          if ( ! method_exists($this->CI, $rule))
          {
            continue;
          }

          // Run the function and grab the result
          $result = $this->CI->$rule($postdata, $param);
        }

        // <<< END of modification

        // Re-assign the result to the master data array
        if ($_in_array == TRUE)
        {
                $this->_field_data[$row['field']]['postdata'][$cycles] = (is_bool($result)) ? $postdata : $result;
        }
        else
        {
                $this->_field_data[$row['field']]['postdata'] = (is_bool($result)) ? $postdata : $result;
        }

        // If the field isn't required and we just processed a callback we'll move on...
        if ( ! in_array('required', $rules, TRUE) AND $result !== FALSE)
        {
                return;
        }
      }
      else
      {
        if ( ! method_exists($this, $rule))
        {
          // If our own wrapper function doesn't exist we see if a native PHP function does.
          // Users can use any native PHP function call that has one param.
          if (function_exists($rule))
          {
            $result = $rule($postdata);

            if ($_in_array == TRUE)
            {
              $this->_field_data[$row['field']]['postdata'][$cycles] = (is_bool($result)) ? $postdata : $result;
            }
            else
            {
              $this->_field_data[$row['field']]['postdata'] = (is_bool($result)) ? $postdata : $result;
            }
          }

          continue;
        }

        $result = $this->$rule($postdata, $param);

        if ($_in_array == TRUE)
        {
          $this->_field_data[$row['field']]['postdata'][$cycles] = (is_bool($result)) ? $postdata : $result;
        }
        else
        {
          $this->_field_data[$row['field']]['postdata'] = (is_bool($result)) ? $postdata : $result;
        }
      }

      // Did the rule test negatively?  If so, grab the error.
      if ($result === FALSE)
      {
        if ( ! isset($this->_error_messages[$rule]))
        {
          if (FALSE === ($line = $this->CI->lang->line($rule)))
          {
            $line = 'Unable to access an error message corresponding to your field name.';
          }
        }
        else
        {
          $line = $this->_error_messages[$rule];
        }

        // Is the parameter we are inserting into the error message the name
        // of another field?  If so we need to grab its "field label"
        if (isset($this->_field_data[$param]) AND isset($this->_field_data[$param]['label']))
        {
          $param = $this->_field_data[$param]['label'];
        }

        // Build the error message
        $message = sprintf($line, $this->_translate_fieldname($row['label']), $param);

        // Save the error message
        $this->_field_data[$row['field']]['error'] = $message;

        if ( ! isset($this->_error_array[$row['field']]))
        {
          $this->_error_array[$row['field']] = $message;
        }

        return;
      }
    }
  }
	
  /**
   * Match a basic regex pattern.
   * Delimiters optional; only # or / supported as delimiters.
   * Supports only the caseless modifier flag 'i', 
   * but in that case the delimiters are required.
   * 
   * @param $str
   * @param $pattern
   * @return bool
   */
  function regex_match($str, $pattern) 
  {	  
	//remove leading/trailing whitespace
	$pattern = trim($pattern);
	  
	//check for case-insensitive flag.  Other flags not supported in this method.
	$i = '';
    if(in_array($pattern{0},array('/','#')) AND substr($pattern, -2)=='i')
    {
	    $i = 'i';
	    $pattern = rtrim($pattern,'i');
    }

	//remove '/' or '#' that might have been included as pattern delims
	//and assemble the final pattern
    $pattern = '/^' . trim($pattern,'/#') . '$/'.$i;
	  
	//do the match
    if (preg_match($pattern, $str)) return TRUE;
    return FALSE;
  }

  /**
   * Basic rule to determine if a string is a valid url.
   * This covers 95% of URLs, but some arcane URLs will come up false.
   * Use regex_match with a longer regex, such as Gruber v2, for more exhaustive coverage.
   * @link http://daringfireball.net/2010/07/improved_regex_for_matching_urls
   * 
   * @param string $str
   * @return bool
   */
  function valid_url($str)
  {
	//simple url regex
	$ptn = '([\<]?)((http(?:s)?\:\/\/)?[a-zA-Z0-9\-]+(?:\.[a-zA-Z0-9\-]+)*\.[a-zA-Z]{2,6}(?:\/?|(?:\/[\w\-]+)*)'
	       .'(?:\/?|\/\w+\.[a-zA-Z]{2,4}(?:\?[\w]+\=[\w\-]+)?)?(?:\&[\w]+\=[\w\-]?(\&)*)*)([\>]?)';  
    
	return $this->regex_match($str, $ptn);
  }
}
// --------------------------------------------------------------------
/**
 * End of FF_validator
 */
