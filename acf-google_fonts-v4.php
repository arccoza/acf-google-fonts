<?php

class acf_field_google_fonts extends acf_field {
	
	// vars
	var $settings, // will hold info such as dir / path
		$defaults; // will hold default field options
		
		
	/*
	*  __construct
	*
	*  Set name / label needed for actions / filters
	*
	*  @since	3.6
	*  @date	23/01/13
	*/
	
	function __construct()
	{
		// vars
		$this->name = 'google_fonts';
		$this->label = __('Google Fonts');
		$this->category = __("Choice",'acf'); // Basic, Content, Choice, etc
		$this->defaults = array(
			// add default here to merge into your field. 
			// This makes life easy when creating the field options as you don't need to use any if( isset('') ) logic. eg:
			//'preview_size' => 'thumbnail'
		);
		
		
		// do not delete!
    	parent::__construct();
    	
    	
    	// settings
		$this->settings = array(
			'path' => \apply_filters('acf/helpers/get_path', __FILE__),
			'dir' => \apply_filters('acf/helpers/get_dir', __FILE__),
			'version' => '1.0.0'
		);
	}
	
	
	/*
	*  create_options()
	*
	*  Create extra options for your field. This is rendered when editing a field.
	*  The value of $field['name'] can be used (like below) to save extra data to the $field
	*
	*  @type	action
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	$field	- an array holding all the field's data
	*/
	
	function create_options( $field )
	{
		// defaults?
		/*
		$field = array_merge($this->defaults, $field);
		*/
		
		// key is needed in the field names to correctly save the data
		$key = $field['name'];
		
		// Create Field Options HTML
		?>
<tr class="field_option field_option_<?php echo $this->name; ?>">
	<td class="label">
		<label><?php _e('Google API Key','acf'); ?></label>
		<p class="description"><?php _e('Enter your Google Fonts API Key.','acf'); ?></p>
	</td>
	<td>
		<?php
		$api_key = \get_transient('acf_google_fonts_api_key');

		\do_action('acf/create_field', array(
			'type'		=>	'text',
			'name'		=>	'fields['.$key.'][google_api_key]',
			'value'		=>	$field['google_api_key'] ?: $api_key,
			'layout'	=>	'horizontal'
		));
		
		if(isset($field['google_api_key'])) {
			\set_transient('acf_google_fonts_api_key', $field['google_api_key']);
		}
		?>
	</td>
</tr>
		<?php
		
	}
	
	
	/*
	*  create_field()
	*
	*  Create the HTML interface for your field
	*
	*  @param	$field - an array holding all the field's data
	*
	*  @type	action
	*  @since	3.6
	*  @date	23/01/13
	*/
	
	function create_field( $field )
	{
		// defaults?
		/*
		$field = array_merge($this->defaults, $field);
		*/
		var_dump(\get_transient('acf_google_fonts_queue'));
		// perhaps use $field['preview_size'] to alter the markup?
		$api_key = $field['google_api_key'];
		if(!isset($api_key)) {
			?>
				<span style="color:red;">A Google API key is required.</span>
			<?php

			return;
		}

		$fonts = \get_transient('acf_google_fonts_json');

		if($fonts === false) {
			$refer = (isset($_SERVER['HTTPS']) ? 'https://' : 'http://').$_SERVER['HTTP_HOST'];
			$hdr = array(
				'http'=>array(
				'method'=>'GET',
				'header'=>"Referer: " . $refer
				)
			);
			$ctx = stream_context_create($hdr);
			$res = file_get_contents('https://www.googleapis.com/webfonts/v1/webfonts?key=AIzaSyAl5i_oiD72i0D005-kMGydACcx0pMft4k', false, $ctx);

			\set_transient('acf_google_fonts_json', $res, 60 * 60 * 24 * 5); //DAY_IN_SECONDS
			$fonts = $res;
		}

		$fonts = \json_decode($fonts, true);
		
		// create Field HTML
		?>
		<div id="<?php echo $field['id']; ?>" 
			class="<?php echo $field['class']; ?> box">

			<div style="display:table-cell;">
				<select name="<?php echo $field['name'] . '[font]'; ?>" 
					class="<?php echo $field['class']; ?> fonts">

					<option style="color:#ccc;">
						- Fonts -
					</option>
					<?php foreach($fonts['items'] as $font): ?>
						<?php 
							$weights = [];
							foreach ($font['variants'] as $variant) {
								$matches = [];
								preg_match('/(\d{3})(italic)?|(^regular$|^italic$)/', $variant, $matches);

								if($matches[3] == 'regular' || $matches[3] == 'italic') {
									$weights['400'] = $matches[3] == 'italic' ? ['normal', 'italic'] : ['normal'];
								}
								elseif(!empty($matches[1])) {
									$weights[$matches[1]] = !empty($matches[2]) ? ['normal', $matches[2]] : ['normal'];
								}
							}
						?>

						<option <?php echo $field['value']['font'] == $font['family'] ? 'selected' : ''; ?> 
							value="<?php echo $font['family']; ?>" 
							data-variants='<?php echo json_encode($weights); ?>'>
							<?php echo $font['family']; ?>
						</option>
					<?php endforeach; ?>
				</select>
			</div>

			<div style="display:table-cell; padding-left:0.5em;">
				<select name="<?php echo $field['name'] . '[weight]'; ?>" 
					data-stored-value="<?php echo $field['value']['weight']; ?>" 
					class="<?php echo $field['class']; ?> font-weights">

					<option value="400" style="color:#ccc;">
						- Weights -
					</option>
				</select>
			</div>

			<div style="display:table-cell; padding-left:0.5em;">
				<select name="<?php echo $field['name'] . '[style]'; ?>" 
					data-stored-value="<?php echo $field['value']['style']; ?>" 
					class="<?php echo $field['class']; ?> font-styles">

					<option value="normal" style="color:#ccc;">
						- Styles -
					</option>
				</select>
			</div>

		</div>
		<?php
	}
	
	
	/*
	*  input_admin_enqueue_scripts()
	*
	*  This action is called in the admin_enqueue_scripts action on the edit screen where your field is created.
	*  Use this action to add CSS + JavaScript to assist your create_field() action.
	*
	*  $info	http://codex.wordpress.org/Plugin_API/Action_Reference/admin_enqueue_scripts
	*  @type	action
	*  @since	3.6
	*  @date	23/01/13
	*/

	function input_admin_enqueue_scripts()
	{
		// Note: This function can be removed if not used
		
		
		// register ACF scripts
		wp_register_script( 'acf-input-google_fonts', $this->settings['dir'] . 'js/input.js', array('acf-input'), $this->settings['version'] );
		wp_register_style( 'acf-input-google_fonts', $this->settings['dir'] . 'css/input.css', array('acf-input'), $this->settings['version'] ); 
		
		
		// scripts
		wp_enqueue_script(array(
			'acf-input-google_fonts',	
		));

		// styles
		wp_enqueue_style(array(
			'acf-input-google_fonts',	
		));
		
		
	}
	
	
	/*
	*  input_admin_head()
	*
	*  This action is called in the admin_head action on the edit screen where your field is created.
	*  Use this action to add CSS and JavaScript to assist your create_field() action.
	*
	*  @info	http://codex.wordpress.org/Plugin_API/Action_Reference/admin_head
	*  @type	action
	*  @since	3.6
	*  @date	23/01/13
	*/

	function input_admin_head()
	{
		// Note: This function can be removed if not used
	}
	
	
	/*
	*  field_group_admin_enqueue_scripts()
	*
	*  This action is called in the admin_enqueue_scripts action on the edit screen where your field is edited.
	*  Use this action to add CSS + JavaScript to assist your create_field_options() action.
	*
	*  $info	http://codex.wordpress.org/Plugin_API/Action_Reference/admin_enqueue_scripts
	*  @type	action
	*  @since	3.6
	*  @date	23/01/13
	*/

	function field_group_admin_enqueue_scripts()
	{
		// Note: This function can be removed if not used
	}

	
	/*
	*  field_group_admin_head()
	*
	*  This action is called in the admin_head action on the edit screen where your field is edited.
	*  Use this action to add CSS and JavaScript to assist your create_field_options() action.
	*
	*  @info	http://codex.wordpress.org/Plugin_API/Action_Reference/admin_head
	*  @type	action
	*  @since	3.6
	*  @date	23/01/13
	*/

	function field_group_admin_head()
	{
		// Note: This function can be removed if not used
	}


	/*
	*  load_value()
	*
		*  This filter is applied to the $value after it is loaded from the db
	*
	*  @type	filter
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	$value - the value found in the database
	*  @param	$post_id - the $post_id from which the value was loaded
	*  @param	$field - the field array holding all the field options
	*
	*  @return	$value - the value to be saved in the database
	*/
	
	function load_value( $value, $post_id, $field )
	{
		// Note: This function can be removed if not used
		return $value;
	}
	
	
	/*
	*  update_value()
	*
	*  This filter is applied to the $value before it is updated in the db
	*
	*  @type	filter
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	$value - the value which will be saved in the database
	*  @param	$post_id - the $post_id of which the value will be saved
	*  @param	$field - the field array holding all the field options
	*
	*  @return	$value - the modified value
	*/
	
	function update_value( $value, $post_id, $field )
	{
		// Note: This function can be removed if not used
		$queue = json_decode(\get_transient('acf_google_fonts_queue') ?: '{}', true);
		$font = str_replace(' ', '+', $value['font']);
		$weight = $value['weight'];
		$style = $value['style'] == 'italic' ? 'italic' : '';
		//$queue[$font][$weight.$style] = $post_id;

		$queue['post_id:'.$post_id][$field['_name']] = $post_id;

		\set_transient('acf_google_fonts_queue', json_encode($queue));

		return $value;
	}
	
	
	/*
	*  format_value()
	*
	*  This filter is applied to the $value after it is loaded from the db and before it is passed to the create_field action
	*
	*  @type	filter
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	$value	- the value which was loaded from the database
	*  @param	$post_id - the $post_id from which the value was loaded
	*  @param	$field	- the field array holding all the field options
	*
	*  @return	$value	- the modified value
	*/
	
	function format_value( $value, $post_id, $field )
	{
		// defaults?
		/*
		$field = array_merge($this->defaults, $field);
		*/
		
		// perhaps use $field['preview_size'] to alter the $value?
		
		
		// Note: This function can be removed if not used
		return $value;
	}
	
	
	/*
	*  format_value_for_api()
	*
	*  This filter is applied to the $value after it is loaded from the db and before it is passed back to the API functions such as the_field
	*
	*  @type	filter
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	$value	- the value which was loaded from the database
	*  @param	$post_id - the $post_id from which the value was loaded
	*  @param	$field	- the field array holding all the field options
	*
	*  @return	$value	- the modified value
	*/
	
	function format_value_for_api( $value, $post_id, $field )
	{
		// defaults?
		/*
		$field = array_merge($this->defaults, $field);
		*/
		
		// perhaps use $field['preview_size'] to alter the $value?
		
		
		// Note: This function can be removed if not used
		return $value;
	}
	
	
	/*
	*  load_field()
	*
	*  This filter is applied to the $field after it is loaded from the database
	*
	*  @type	filter
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	$field - the field array holding all the field options
	*
	*  @return	$field - the field array holding all the field options
	*/
	
	function load_field( $field )
	{
		// Note: This function can be removed if not used
		return $field;
	}
	
	
	/*
	*  update_field()
	*
	*  This filter is applied to the $field before it is saved to the database
	*
	*  @type	filter
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	$field - the field array holding all the field options
	*  @param	$post_id - the field group ID (post_type = acf)
	*
	*  @return	$field - the modified field
	*/

	function update_field( $field, $post_id )
	{
		// Note: This function can be removed if not used
		return $field;
	}

	
}


// create field
new acf_field_google_fonts();

?>
