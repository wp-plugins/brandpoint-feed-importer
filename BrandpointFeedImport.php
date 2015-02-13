<?php
/**
 * @package Brandpoint Feed Importer
 * @version 1.0
 */
/*
Plugin Name: Brandpoint Feed Importer
Plugin URI: http://wordpress.org/plugins/BrandpointFeedImporter/
Description: This plugin allows you to import articles and other content written by Brandpoint or any other source using the Brandpoint custom feed format.
Author: Joel Leger
Version: 1.0
Author URI: http://www.brandpoint.com/
*/

function bptfi_admin_init() 
{
       wp_register_style('bootstrapcss', plugins_url('/css/bootstrap.min.css', __FILE__) );
       wp_enqueue_style('bootstrapcss');
       wp_register_style('bptfistyle', plugins_url('/css/bptfi_style.css', __FILE__) );
       wp_enqueue_style('bptfistyle');       
       wp_enqueue_script('jquery');
       wp_register_script( 'bootstrapjs', plugins_url( '/js/bootstrap.min.js', __FILE__ ) );
       wp_enqueue_script('bootstrapjs');
}
add_action( 'admin_enqueue_scripts', 'bptfi_admin_init' );

// add the admin menu
function bptfi_plugin_menu() {
	add_options_page( 'Brandpoint Feed Importer', 'Brandpoint Feed Importer', 'manage_options', 'bptfeedimporter', 'bptfi_plugin_options' );
}
add_action( 'admin_menu', 'bptfi_plugin_menu' );


// grab all the options for this plugin and drop them into the $options array for later reference
function bptfi_plugin_options() 
{
	if ( !current_user_can( 'manage_options' ) )  
	{
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}
	$bptAllOptions = get_option("bpt_feedUrl_Data");
	if (!empty($bptAllOptions))
	{ 
		foreach ($bptAllOptions as $bptKey => $bptOption)
	    {
	       	$bptOptions[$bptKey] = $bptOption;
	    }
	}
?>


	<script>
		var bptCounter = <?php echo count($bptOptions); ?>;
		jQuery(window).ready(function($){ $("#bptMessageBar").hide();})
		jQuery(document).ready(function($){
		    $("#bptErrorBar").hide();

			$("#btnAddFeed").click(function(){
				$( "<input type='text' style='width:30%;' id='bpt_feedUrl_" + bptCounter + "' name='bpt_feedUrl_" + bptCounter + "'><br/>" ).insertBefore( $(this) );
				bptCounter++;
			});

			// populate settings from the DB
			$("#assignToUser").val('<?php echo $bptOptions['assignToUser']; ?>');
			$("#assignToCat").val('<?php echo $bptOptions['assignToCat']; ?>');
			$("#intervalCron").val('<?php echo $bptOptions['intervalCron']; ?>');

			$("#bptSaveButton").click(function(){
				$.post("<?php echo get_option('siteurl'); ?>/wp-admin/admin-ajax.php", 
					{
						action: "bpt_save_data", 'cookie': encodeURIComponent(document.cookie),
						'authorId': $("#assignToUser").val(), 
						'categoryId': $('#assignToCat').val(),
						'intervalCron': $('#intervalCron').val(),
						data:$("#bptFeedForm").serialize()
					}).done(function(msg)
                    {
                        if(msg == "success")
                        {
                           $("#bptMessageBar").show().delay(5000).fadeOut();
                        }
                        else
                        {
                            $("#bptErrorBar").show().delay(5000).fadeOut();
                        }
                    });

			});
			$("#bptImportNow").click(function(){ 
				$.post("<?php echo get_option('siteurl'); ?>/wp-admin/admin-ajax.php", 
					{
						action: "bpt_import_data", 
						'cookie': encodeURIComponent(document.cookie), 
						'authorId': $("#assignToUser").val(), 
						'categoryId': $('#assignToCat').val()
					}).done(function(msg)
					{
					    if(msg == "success")
					    {
					       $("#bptMessageBar").show().delay(5000).fadeOut();
					    }
					    else
					    {
					        $("#bptErrorBar").show().delay(5000).fadeOut();
					    }
					});
			});
			$("#bpt_feedUrl_0").keyup(function()
			{
                urlVal = $(this).val();
                if(urlVal.length > 0)
                {
                    if((urlVal.toLowerCase().indexOf("format=xml") != -1) && (urlVal.toLowerCase().indexOf("platform.brandpoint.com") != -1))
                    {
                        $("#bptErrorBar").hide();
                    }
                    else 
                    {
                        $("#bptErrorBar").show();
                    }
			    } 
			    else
			    {
                    $("#bptErrorBar").hide();
			    }
			});
			
		});
	</script>
	<h2>Brandpoint Feed Importer</h2>
	<p>Brandpoint provides high-quality content for your blog.  Don't have time to blog?  Need more content than you have time to write?  Contact <a href="http://www.brandpoint.com" target="_blank">Brandpoint</a> and we can help you get a content feed setup.</p>
	<p>&nbsp;</p>
		<form id="bptFeedForm" class="form-horizontal">
            <div class="form-group">
                <label for="assignToUser" class="col-sm-2 control-label text-left">Author Assignment:</label>
                <div class="col-sm-3">
                    <select id="assignToUser" class="form-control" name="assignToUser">
                	<?php
                		// get list of authors and above
                		$bptArgs = array(
                			'order by' => 'display_name',
                			'who' => 'authors'
                		);
                		$bptAuthors = get_users( $bptArgs );
                		foreach ( $bptAuthors as $bptUser ) 
                		{
                			echo '<option value="' . esc_html( $bptUser->ID ) . '">' . esc_html( $bptUser->display_name ) . '</option>';
                		}
                	?>
                   </select>
               </div>
           </div>
           <div class="form-group">
               <label for="assignToCat" class="col-sm-2 control-label text-left">Post Category:</label> 
               <div class="col-sm-3">
                	<select id="assignToCat" class="form-control" name="assignToCat">
                	<?php
                		// get list of authors and above
                		$bptArgs = array(
                			'orderby' => 'name',
                			'order' => 'ASC',
                			'hide_empty' => 0
                		);
                		$bptCats = get_categories( $bptArgs );
                		foreach ( $bptCats as $bptCat ) 
                		{
                			echo '<option value="' . esc_html( $bptCat->cat_ID ) . '">' . esc_html( $bptCat->name ) . '</option>';
                		}
                	?>
                	</select>
               </div>
               <?php $bptAdminUrl = admin_url( 'edit-tags.php?taxonomy=category' ); ?>
               <div class="col-sm-3"><a href="<?php echo $bptAdminUrl; ?>">Manage Post Categories</a></div>
           </div>
           <div class="form-group">
                <label for="intervalCron" class="col-sm-2 control-label text-left">Import Frequency:</label> 
                <div class="col-sm-3">
                    <select id="intervalCron" class="form-control" name="intervalCron">
                        <option value="1">Every Hour</option>
                        <option value="2">Every 6 Hours</option>
                        <option value="3">Every 12 Hours</option>
                        <option value="4">Every 24 Hours</option>
                    </select>
                	<?php 
                		date_default_timezone_set(get_option('timezone_string'));
                		$bptDate = wp_next_scheduled('bpt_feedUrl_pull_task', array());
                		$bptDate = ($bptDate == false) ? "Never" : date("Y-m-d H:i:s", $bptDate);
                        if($bptDate > date('now'))
                        {
                            echo "Not currently scheduled";
                        }
                        else 
                        {
                            echo 'Next scheduled import: ' . $bptDate;
                        }
                	?>
                </div>
            </div> 

            <div class="form-group">
                <label for="bpt_feedUrl_0" class="col-sm-2 control-label text-left">Brandpoint Feed Url: </label>
                <div class="col-sm-5">              		
                    <input type='text' id='bpt_feedUrl_0' class="form-control" name='bpt_feedUrl_0' value='<?php echo $bptOptions['bpt_feedUrl_0']; ?>'>
                </div>
            </div>
            <div class="form-group">
                <div class="col-sm-4 col-sm-offset-2">
        		<input type="button" id="bptSaveButton" class="btn btn-primary" value="Save Settings" />
        		<input type="button" id="bptImportNow" class="btn btn-primary" value="Import Feed Now" />
        		<p class="bg-danger errorBar" id="bptErrorBar">That does not appear to be a valid Brandpoint url</p>
        		<p class="bg-success successBar" id="bptMessageBar">Changes Saved!</p>
        		</div>
			</div>
	   </form>
        <p>*** If your content doesn't seem to be getting pulled in, at the prescribed interval, you can <em>try</em> adding the following to your wp-config.php file: <br/>
           define('ALTERNATE_WP_CRON', true);
        </p>

	<?php
}

// add a six hour cron option
function bpt_add_weekly_schedule( $bptSchedules ) 
{
	$bptSchedules['sixhours'] = array(
		'interval' => 21600, // 6 hours
		'display' => __( 'Six Hours', 'BrandpointFeedImport' )
  	);
	return $bptSchedules;
}
add_filter( 'cron_schedules', 'bpt_add_weekly_schedule' ); 

// clear the existing cron and setup a new one using the user-selected interval, but defaulting the "daily" 
function bpt_feedUrl_pull_schedule($bptInterval){
	wp_clear_scheduled_hook( 'bpt_feedUrl_pull_task' );
	//Use wp_next_scheduled to check if the event is already scheduled
	$bptTimestamp = wp_next_scheduled( 'bpt_feedUrl_pull_task' );
	$bptIntervalStr = 'daily';
	switch($bptInterval)
	{
		case 1:
			$bptIntervalStr = 'hourly';
			break;
		case 2:
			$bptIntervalStr = 'sixhours';
			break;
		case 3:
			$bptIntervalStr = 'twicedaily';
			break;
		case 4:
			$bptIntervalStr = 'daily';
			break;
	}
	if( $bptTimestamp == false ){
		wp_schedule_event( time(), $bptIntervalStr, 'bpt_feedUrl_pull_task' );
	}
}
add_action( 'bpt_feedUrl_pull_task', 'bptImportData' );

// this is triggered when the save button is clicked via Ajax. It will update the chron schedule for the importer, and save other values.
function bptSaveData()
{
	global $wpdb; 	
	global $userdata;
	
	// dump the request object into $output	
	parse_str($_REQUEST['data'], $bptOutput);
	// filter out the blanks and put valid values into $response
	foreach($bptOutput as $bptKey => $bptVal)
	{
		if($bptVal != "")
		{
			$bptResponse[$bptKey] = $bptVal;
		}
	}	
	// update/create the option
 	update_option('bpt_feedUrl_Data', $bptResponse);
 	$bptOptions = get_option("bpt_feedUrl_Data");
 	bpt_feedUrl_pull_schedule((int)$bptOptions['intervalCron']);
 	echo "success";
 	exit;
}
add_action('wp_ajax_bpt_save_data', 'bptSaveData');

// when the import button is clicked (or the cron runs)
function bptImportData()
{
	global $wpdb; 	
	global $userdata;
	$bptOptions = get_option("bpt_feedUrl_Data");
	if($bptOptions["bpt_feedUrl_0"] == "")
    {
        echo "error";
        exit;
    }
    else 
    {
        $bptBody = wp_remote_get($bptOptions['bpt_feedUrl_0']);
        // pass this to the xml parser
        parseXml($bptBody["body"]);
        // update the option in the DB
        update_option("bpt_feedUrl_Data", $bptOptions);
        echo "success";
        exit;
    }
}
add_action('wp_ajax_bpt_import_data', 'bptImportData');

// parse the xml and create the posts in the DB if needed
function parseXml($xmlString)
{
	//$bptAllIds = array();
	$bptOptions = get_option("bpt_feedUrl_Data");

	$bptAuthorId = $bptOptions['bpt_feedUrl_authorId'];
	$bptCategoryId[] = $bptOptions['bpt_feedUrl_categoryId'];

	// get a collection of all of the existing posts and create a hash of using the title as the key and the post date as the value.
	// because PHP does not enforce unique keys, if there are two posts with the same title, it will take the last one.
	$bptArgs = array('post_type' => 'post','posts_per_page' => '9999');
	$bptPostsArray = get_posts( $bptArgs );

	// load XML and iterate the object
	$bptXml = simplexml_load_string($xmlString, 'SimpleXMLElement', LIBXML_NOCDATA);
	foreach($bptXml->items->item as $bptItem)
	{
		$bptTitle = html_entity_decode($bptItem->title);
		$bptBody = html_entity_decode($bptItem->item_body);
		$bptReleaseDate = $bptItem->release_date;

		if(isset($bptItem->metas->meta))
		{
			foreach($bptItem->metas->meta as $bptMeta)
			{
				$bptMetaDesc = ((string)$bptMeta->attributes()->name == 'Description') ? (string)$bptMeta : $bptMetaDesc;
				$bptSeoTitle = ((string)$bptMeta->attributes()->name == 'SEOTitle') ? (string)$bptMeta : $bptSeoTitle;
				$bptFocusKeys = ((string)$bptMeta->attributes()->name == 'FocusKeywords') ? (string)$bptMeta : $bptFocusKeys;
			}
		}

		if(isset($bptItem->keywords->keyword))
		{
			foreach($bptItem->keywords->keyword as $bptKey)
			{
				$bptKeys[] = (string)$bptKey;	
			}
		}
		$bptPostdate = date("Y-m-d H:i:s", strtotime($bptReleaseDate));
		$bptPostArgs = array(
			'post_content'   => $bptBody, // The full text of the post.
			'post_name'      => $bptTitle, // The name (slug) for your post
			'post_title'     => $bptTitle, // The title of your post.
			'post_status'    => 'publish', //[ 'draft' | 'publish' | 'pending'| 'future' | 'private' | custom registered status ] // Default 'draft'.
			'post_type'      => 'post', //[ 'post' | 'page' | 'link' | 'nav_menu_item' | custom post type ] // Default 'post'.
			'post_author'    => $bptAuthorId, // The user ID number of the author. Default is the current user ID.
			'ping_status'    => 'closed', //[ 'closed' | 'open' ] // Pingbacks or trackbacks allowed. Default is the option 'default_ping_status'.
			'post_excerpt'   => $bptBody, // For all your post excerpt needs.
			'post_date'      => $bptPostdate, //;[ Y-m-d H:i:s ] // The time post was made.
			'post_date_gmt'  => gmdate("Y-m-d H:i:s", strtotime($bptReleaseDate)), //[ Y-m-d H:i:s ] // The time post was made, in GMT.
			'comment_status' => 'closed', //[ 'closed' | 'open' ] // Default is the option 'default_comment_status', or 'closed'.
			'post_category'  => $bptCategoryId, //[ array(<category id>, ...) ] // Default empty.
			'tags_input'     => $bptKeys // Default empty.
		);
        $bptMarker = "false";
        foreach($bptPostsArray as $p)
        {
            if($bptTitle === $p->post_title)
            {
                $bptMarker = "true";
            }
        }
        
        if($bptMarker == "false")
        {
			$bptPostId = wp_insert_post($bptPostArgs);
		
            if(is_plugin_active('wordpress-seo/wp-seo.php')) // if Yoast is installed and active
            { 
                update_post_meta($bptPostId, '_yoast_wpseo_metadesc', $bptMetaDesc, ""); 
                update_post_meta($bptPostId, '_yoast_wpseo_title', $bptSeoTitle, ""); 
                update_post_meta($bptPostId, '_yoast_wpseo_focuskw', $bptFocusKeys, ""); 
            } 

            if(isset($bptItem->assets->asset))
            {
                foreach($bptItem->assets->asset as $bptAsset)
                {
                    // only upload the original file and let Wordpress make thumbs etc.
                    if((string)$bptAsset->attributes()->assetsize == 'original')
                    {
                        AddImageToPost((string)$bptAsset, $bptPostId);
                    }
                }
            }
        }
		// reset all these vars to keep them from carrying over to the next record
		$bptMarker = "false";
		$bptKeys = array();
		$bptBody = '';
		$bptTitle = '';
		$bptReleaseDate = '';
		$bptMetaDesc = '';
		$bptSeoTitle = '';
		$bptFocusKeys = '';
	}
}

function AddImageToPost($image_url, $post_id)
{
	// Add Featured Image to Post
	$upload_dir = wp_upload_dir(); // Set upload folder
	$image_data = file_get_contents($image_url); // Get image data
	$filename   = basename($image_url); // Create image file name

	// Check folder permission and define file location
	if( wp_mkdir_p( $upload_dir['path'] ) ) {
	    $file = $upload_dir['path'] . '/' . $filename;
	} else {
	    $file = $upload_dir['basedir'] . '/' . $filename;
	}

	// Create the image  file on the server
	file_put_contents( $file, $image_data );

	// Check image file type
	$wp_filetype = wp_check_filetype( $filename, null );

	// Set attachment data
	$attachment = array(
	    'post_mime_type' => $wp_filetype['type'],
	    'post_title'     => sanitize_file_name( $filename ),
	    'post_content'   => '',
	    'post_status'    => 'inherit'
	);

	// Create the attachment
	$attach_id = wp_insert_attachment( $attachment, $file, $post_id );

	// Include image.php
	require_once(ABSPATH . 'wp-admin/includes/image.php');

	// Define attachment metadata
	$attach_data = wp_generate_attachment_metadata( $attach_id, $file );

	// Assign metadata to attachment
	wp_update_attachment_metadata( $attach_id, $attach_data );

	// And finally assign featured image to post
	set_post_thumbnail( $post_id, $attach_id );
}

