# FluentForm

This _advanced_ version of Formbuiler was intended as a from-scratch rewrite of original @dperrymorrow's spark, but it quickly evolved in a completely different beast.
Its goal is to create a full-fledged form-factory able to define, render and validate a form, providing at the very same time a fluent interface so that form creation is easy to write and read.
Full doc will be available soon, in the meantime here is a simple example.

Create a controller named at your wish, and add the following code to it:

    $this->load->spark('fluentform/2.0.0');

    $form = $this->fluentform->new_form();

    $form->set_action('page/home')
    ->add_file_field('image','Upload image')
    ->add_group('account', 'Account')
      ->add_text_field('username', 'Username', '', 'class="username red" id="username"', 'trim|required|min_length[5]|xss_clean')
      ->add_password_field('password', 'Password', '', 'id="password"', 'trim|min_length[5]|xss_clean')
      ->add_email_field('email', 'E-Mail', 'example@example.com', array(), TRUE)
    ->add_group('profile', 'Profile')
      ->add_text_field('name', 'Name')
      ->add_checkbox('happy', 'I am happy', FB_R_CRB_LABEL_AFTER, TRUE, array('class'=>array('check', 'happy'), 'id'=>'mycheck'))
    ->close_group()
    ->add_checkboxes('hero', 'My favorite comic hero:', FB_R_CRB_LABEL_AFTER, array(
      array (
        'label' => 'Superman',
        'value' => 'superman',
        'checked' => FALSE,
      ),
      array (
        'label' => 'Goofy',
        'value' => 'goofy',
        'checked' => FALSE,
      ),
      array (
        'label' => 'Conan the Barbarian',
        'value' => 'conan',
        'checked' => FALSE,
      )))
    ->add_submit('submit', 'Save')
    ->add_reset('reset', 'Cancel');

    if (!$form->validate())
    {
      echo $form; // magic method to print the form out straight away, or
      //echo $myform->render_form();
    }
    else
    {
      echo "Nice form!";
    }

Some nice feature that will soon be documented in further details:

- Validator extends standard CI one so that you could put your custom validation callbacks in the model. Just add `"callback_myfunction_model[mymodel]"` into validation rules to invoke `$this->mymodel->myfunction()` as a validation method.
- Renderer and validator could be rewritten if necessary: I plan to provide an interface to inherit from withh the stable release. The goal is to allow anybody to implement other renderers (for integration with template engines and such) and maybe more advanced validators. Even if decoupling is not perfect yet, a couple of public functions are already available to inject your custom objects:

    $form = $this->fluentform->new_form();

    $form->set_validator(new Custom_validator($form));
    $form_>set_renderer(new Custom_renderer($form));

- You could print out the whole form just echoing the object or treating it as a string; still you could access following methods:

    echo $form->render_form(); // echoes the form, equals echo $form;
    echo $form->render_group('profile'); // echoes specified fieldset;
    echo $form->render_field('name'); // echoes specified field, wrapped by chosen wrapper tags (specified in config file)
    echo $form->render_raw_field('name'); //echoes specified field, without wrapper but full with label and error

- In config file you could chose label positions for checkboxed and radiobuttons (before/after/none), validation error method (general/field-by-field) and error messages positions (before/after form open; before/after form close OR before field label/between label and field/after field). You could also configure error wrapper tags and classes.

# Questions and Answers

**Q:** This doc is crap! When REAL docs will be available?  
**A:** Complete CI-Styled docs will be released together with upcoming version 2.1, that will feature some more fluent-magic and features.

**Q:** How does FB compare to FormGen (issue #4)? Benefits of both?  
**A:** A complete comparison with other solutions will hopefully be available in the upcoming docs. In the meantime, take a look here: https://github.com/stickgrinder/Formbuilder/issues/5

**Q:** Where could I find FF 1.0?  
**A:** Nowhere. FF was formerly named Formbuilder 2.0 since it was intended as a new version of David Morrow's Frombuilder. I decided to keep numbering from 2 on for the following reasons:

- I always do a lot of noise about what I do, so someone was expecting a 2.0 version of Formbuilder. I don't want to confuse anyone.
- David Morrow made me realize that I was fed up with CI's form helpers, bloated controllers and so on. I wanted FB's legacy to stand out.
- My evil plot is for you to ask, so that I could draw attention to me and feel renowned! :D

# Contacts

- [Log Issues or Suggestions](https://github.com/stickgrinder/Fluentform/issues)
- [Follow me on Twitter](http://twitter.com/stickgrinder)
- [Read my rants on Tumblr] (http://arantaweek.tumblr.com)

# Credits

Many thanks to @dperrymorrow for inspiring me with his original Formbuilder! :) Kudos!  
Many thanks to @philsturgeon for writing DBAD public licence.

# License

_FluentForm_ is released under the "Don't Be a Dick Public License", which you can read in all its glory here: http://philsturgeon.co.uk/code/dbad-license