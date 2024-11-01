<?php
    /* 
    Plugin Name: WordPress vertical Thumbnail Slider
    Plugin URI:http://www.i13websolution.com/wordpress-pro-plugins/wordpress-vertical-image-slider-pro-plugin.html
    Author URI:http://www.i13websolution.com/wordpress-pro-plugins/wordpress-vertical-image-slider-pro-plugin.html
    Description: This is beautiful thumbnail image slider plugin for WordPress.Add any number of images from admin panel.
    Author:I Thirteen Web Solution 
    Version:1.2
    */

    add_action('admin_menu', 'add_vertical_thumbnail_slider_admin_menu');
   // add_action( 'admin_init', 'my_vertical_thumbnailSlider_admin_init' );
    register_activation_hook(__FILE__,'install_vertical_thumbnailSlider');
    add_action('wp_enqueue_scripts', 'vertical_thumbnail_slider_load_styles_and_js');
    add_shortcode('print_vertical_thumbnail_slider', 'print_vertical_thumbnail_slider_func' );
    add_filter('widget_text', 'do_shortcode');
    add_action('admin_notices', 'vertical_thumbnail_slider_admin_notices');

    function vertical_thumbnail_slider_load_styles_and_js(){
        if (!is_admin()) {                                                       

            wp_enqueue_style( 'images-vertical-thumbnail-slider-style', plugins_url('/css/images-vertical-thumbnail-slider-style.css', __FILE__) );
            wp_enqueue_script('jquery'); 
            wp_enqueue_script('images-vertical-thumbnail-slider-jc',plugins_url('/js/images-vertical-thumbnail-slider-jc.js', __FILE__));

        }  
    }

    function install_vertical_thumbnailSlider(){
        global $wpdb;
        $table_name = $wpdb->prefix . "vertical_thumbnail_slider";

        $sql = "CREATE TABLE " . $table_name . " (
        id int(10) unsigned NOT NULL auto_increment,
        title varchar(1000) NOT NULL,
        image_name varchar(500) NOT NULL,
        createdon datetime NOT NULL,
        custom_link varchar(1000) default NULL,
        post_id int(10) unsigned default NULL,
        PRIMARY KEY  (id)
        );";
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);

        $vertical_thumbnail_slider_settings=array('linkimage' => '1','pauseonmouseover' => '1','auto' =>'','speed' => '1000','circular' => '1','imageheight' => '120','imagewidth' => '120','visible'=> '5','scroll' => '1','resizeImages'=>'0','scollerBackground'=>'#FFFFFF');

        if( !get_option( 'vertical_thumbnail_slider_settings' ) ) {

            update_option('vertical_thumbnail_slider_settings',$vertical_thumbnail_slider_settings);
        } 
        
        
        $uploads = wp_upload_dir();
        $baseDir=$uploads['basedir'];
        $baseDir=str_replace("\\","/",$baseDir);
        $pathToImagesFolder=$baseDir.'/wp-vertical-image-slider';
        wp_mkdir_p($pathToImagesFolder);  

    } 

    
     function vertical_thumbnail_slider_admin_notices(){
        
        $uploads = wp_upload_dir();
        $baseDir=$uploads['basedir'];
        $baseDir=str_replace("\\","/",$baseDir);
        $pathToImagesFolder=$baseDir.'/wp-vertical-image-slider';
        
        $baseurl=$uploads['baseurl'];
        $baseurl.='/wp-vertical-image-slider/';
        
         if (is_plugin_active('wp-vertical-image-slider/wp-vertical-image-slider.php')) {
            
            $uploads = wp_upload_dir();
            $baseDir=$uploads['basedir'];
            $baseDir=str_replace("\\","/",$baseDir);
            $pathToImagesFolder=$baseDir.'/wp-vertical-image-slider';
            
            if(file_exists($pathToImagesFolder) and is_dir($pathToImagesFolder)){
                
                if( !is_writable($pathToImagesFolder)){
                        echo "<div class='updated'><p>Vertical Image Slider is active but does not have write permission on</p><p><b>".$pathToImagesFolder."</b> directory.Please allow write permission.</p></div> ";
                }       
            }
            else{
               
                  wp_mkdir_p($pathToImagesFolder);  
                  if(!file_exists($pathToImagesFolder) and !is_dir($pathToImagesFolder)){
                    echo "<div class='updated'><p>Vertical Image Slider  is active but plugin does not have permission to create directory</p><p><b>".$pathToImagesFolder."</b> .Please create Vertical Image Slider directory inside upload directory and allow write permission.</p></div> "; 
                    
                  }
            }
        }
        
    }
     

    function add_vertical_thumbnail_slider_admin_menu(){

        $hook_suffix_v_l=add_menu_page( __( 'Vertical Thumbnail Slider'), __( 'Vertical Thumbnail Slider' ), 'administrator', 'vertical_thumbnail_slider', 'vertical_thumbnail_slider_admin_options' );
        $hook_suffix_v_l=add_submenu_page( 'vertical_thumbnail_slider', __( 'Slider Setting'), __( 'Slider Setting' ),'administrator', 'vertical_thumbnail_slider', 'vertical_thumbnail_slider_admin_options' );
        $hook_suffix_v_l_1=add_submenu_page( 'vertical_thumbnail_slider', __( 'Manage Images'), __( 'Manage Images'),'administrator', 'vertical_thumbnail_slider_image_management', 'vertical_thumbnail_image_management' );
        $hook_suffix_v_l_2=add_submenu_page( 'vertical_thumbnail_slider', __( 'Preview Slider'), __( 'Preview Slider'),'administrator', 'vertical_thumbnail_slider_preview', 'verticalpreviewSliderAdmin' );

        add_action( 'load-' . $hook_suffix_v_l , 'my_vertical_thumbnailSlider_admin_init' );
        add_action( 'load-' . $hook_suffix_v_l_1 , 'my_vertical_thumbnailSlider_admin_init' );
        add_action( 'load-' . $hook_suffix_v_l_2 , 'my_vertical_thumbnailSlider_admin_init' );
    }

    function my_vertical_thumbnailSlider_admin_init(){

        $url = plugin_dir_url(__FILE__);  

        wp_enqueue_script( 'jquery.validate', $url.'js/jquery.validate.js' );  
        wp_enqueue_script( 'jc', $url.'js/images-vertical-thumbnail-slider-jc.js' );  
        wp_enqueue_style('images-vertical-thumbnail-slider-style',$url.'css/images-vertical-thumbnail-slider-style.css');
        vertical_thumbnail_slider_admin_scripts_init();
        
    }

    function vertical_thumbnail_slider_admin_options(){

        if(isset($_POST['btnsave'])){
            
            if(!check_admin_referer( 'action_settings_add_edit','add_edit_nonce' )){
                
                 wp_die('Security check fail'); 
            }

            $auto=trim($_POST['isauto']);

            if($auto=='auto')
                $auto=true;
            else
                $auto=false; 

            $speed=(int)trim($_POST['speed']);

            if(isset($_POST['circular']))
                $circular=true;  
            else
                $circular=false;  

       
            $visible=trim($_POST['visible']);


            if(isset($_POST['pauseonmouseover']))
                $pauseonmouseover=true;  
            else 
                $pauseonmouseover=false;

            if(isset($_POST['linkimage']))
                $linkimage=true;  
            else 
                $linkimage=false;

            $scroll=trim($_POST['scroll']);

            if($scroll=="")
                $scroll=1;

            $imageheight=(int)trim(htmlentities(strip_tags($_POST['imageheight']),ENT_QUOTES));
            $imagewidth=(int)trim(htmlentities(strip_tags($_POST['imagewidth']),ENT_QUOTES));
            $resizeImages=(int)trim(htmlentities(strip_tags($_POST['resizeImages']),ENT_QUOTES));
            $scollerBackground=trim(htmlentities(strip_tags($_POST['scollerBackground']),ENT_QUOTES));

            $options=array();
            $options['linkimage']=$linkimage;  
            $options['pauseonmouseover']=$pauseonmouseover;  
            $options['auto']=$auto;  
            $options['speed']=$speed;  
            $options['circular']=$circular;  
            //$options['scrollerwidth']=$scrollerwidth;  
            $options['imageheight']=$imageheight;  
            $options['imagewidth']=$imagewidth;  
            $options['visible']=$visible;  
            $options['scroll']=$scroll;  
            $options['resizeImages']=$resizeImages;  
            $options['scollerBackground']=$scollerBackground;  


            $settings=update_option('vertical_thumbnail_slider_settings',$options); 
            $vertical_thumbnail_slider_messages=array();
            $vertical_thumbnail_slider_messages['type']='succ';
            $vertical_thumbnail_slider_messages['message']='Settings saved successfully.';
            update_option('vertical_thumbnail_slider_messages', $vertical_thumbnail_slider_messages);



        }  
        $settings=get_option('vertical_thumbnail_slider_settings');

    ?>      
    <div id="poststuff" >  
        <div id="post-body" class="metabox-holder columns-2">
            <div id="post-body-content">
                <table><tr><td><a href="https://twitter.com/FreeAdsPost" class="twitter-follow-button" data-show-count="false" data-size="large" data-show-screen-name="false">Follow @FreeAdsPost</a>
                            <script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0];if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src="//platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document,"script","twitter-wjs");</script></td>
                        <td>
                            <a target="_blank" title="Donate" href="http://www.i13websolution.com/donate-wordpress_image_thumbnail.php">
                                <img id="help us for free plugin" height="30" width="90" src="<?php echo plugins_url( 'images/paypaldonate.jpg', __FILE__ );?>" border="0" alt="help us for free plugin" title="help us for free plugin">
                            </a>
                        </td>
                    </tr>
                </table>

                <?php
                    $messages=get_option('vertical_thumbnail_slider_messages'); 
                    $type='';
                    $message='';
                    if(isset($messages['type']) and $messages['type']!=""){

                        $type=$messages['type'];
                        $message=$messages['message'];

                    }  


                    if($type=='err'){ echo "<div class='errMsg'>"; echo $message; echo "</div>";}
                    else if($type=='succ'){ echo "<div class='succMsg'>"; echo $message; echo "</div>";}


                    update_option('vertical_thumbnail_slider_messages', array());     
                ?>      

                <span><h3 style="color: blue;"><a target="_blank" href="http://www.i13websolution.com/wordpress-pro-plugins/wordpress-vertical-image-slider-pro-plugin.html">UPGRADE TO PRO VERSION</a></h3></span>

                <h2>Slider Settings</h2>
                <div id="poststuff">
                    <div id="post-body" class="metabox-holder columns-2">
                        <div id="post-body-content">
                            <form method="post" action="" id="scrollersettiings" name="scrollersettiings" >

                                <div class="stuffbox" id="namediv" style="">
                                    <h3><label>Link images with url ?</label></h3>
                                    <div class="inside">
                                        <table>
                                            <tr>
                                                <td>
                                                    <input type="checkbox" id="linkimage" size="30" name="linkimage" value="" <?php if($settings['linkimage']==true){echo "checked='checked'";} ?> style="width:20px;">&nbsp;Add link to image ? 
                                                    <div style="clear:both"></div>
                                                    <div></div>
                                                </td>
                                            </tr>
                                        </table>
                                        <div style="clear:both"></div>
                                    </div>
                                </div>
                                <div class="stuffbox" id="namediv" style="">
                                    <h3><label>Auto Scroll ?</label></h3>
                                    <div class="inside">
                                        <table>
                                            <tr>
                                                <td>
                                                    <input style="width:20px;" type='radio' <?php if($settings['auto']==true){echo "checked='checked'";}?>  name='isauto' value='auto' >Auto &nbsp;<input style="width:20px;" type='radio' name='isauto' <?php if($settings['auto']==false){echo "checked='checked'";} ?> value='manuall' >Scroll By Left & Right Arrow
                                                    <div style="clear:both"></div>
                                                    <div></div>
                                                </td>
                                            </tr>
                                        </table>
                                        <div style="clear:both"></div>
                                    </div>
                                </div>
                                <div class="stuffbox" id="namediv" style="">
                                    <h3><label >Speed</label></h3>
                                    <div class="inside">
                                        <table>
                                            <tr>
                                                <td>
                                                    <input type="text" id="speed" size="30" name="speed" value="<?php echo $settings['speed']; ?>" style="width:100px;">
                                                    <div style="clear:both"></div>
                                                    <div></div>
                                                </td>
                                            </tr>
                                        </table>
                                        <div style="clear:both"></div>

                                    </div>
                                </div>
                                <div class="stuffbox" id="namediv" style="">
                                    <h3><label >Circular Slider ?</label></h3>
                                    <div class="inside">
                                        <table>
                                            <tr>
                                                <td>
                                                    <input type="checkbox" id="circular" size="30" name="circular" value="" <?php if($settings['circular']==true){echo "checked='checked'";} ?> style="width:20px;">&nbsp;Circular Slider ? 
                                                    <div style="clear:both"></div>
                                                    <div></div>
                                                </td>
                                            </tr>
                                        </table>
                                        <div style="clear:both"></div>

                                    </div>
                                </div>
                                <div class="stuffbox" id="namediv" style="">
                                    <h3><label>Slider Background color</label></h3>
                                    <div class="inside">
                                        <table>
                                            <tr>
                                                <td>
                                                    <input type="text" id="scollerBackground" size="30" name="scollerBackground" value="<?php echo $settings['scollerBackground']; ?>" style="width:100px;">
                                                    <div style="clear:both"></div>
                                                    <div></div>
                                                </td>
                                            </tr>
                                        </table>

                                        <div style="clear:both"></div>
                                    </div>
                                </div>
                                <div class="stuffbox" id="namediv" style="">
                                    <h3><label>Visible</label></h3>
                                    <div class="inside">
                                        <table>
                                            <tr>
                                                <td>
                                                    <input type="text" id="visible" size="30" name="visible" value="<?php echo $settings['visible']; ?>" style="width:100px;">
                                                    <div style="clear:both">This will decide your slider width automatically</div>
                                                    <div></div>
                                                </td>
                                            </tr>
                                        </table>
                                        specifies the number of items visible at all times within the slider.
                                        <div style="clear:both"></div>

                                    </div>
                                </div>
                                <div class="stuffbox" id="namediv" style="">
                                    <h3><label>Scroll</label></h3>
                                    <div class="inside">
                                        <table>
                                            <tr>
                                                <td>
                                                    <input type="text" id="scroll" size="30" name="scroll" value="<?php echo $settings['scroll']; ?>" style="width:100px;">
                                                    <div style="clear:both"></div>
                                                    <div></div>
                                                </td>
                                            </tr>
                                        </table>
                                        You can specify the number of items to scroll when you click the next or prev buttons.
                                        <div style="clear:both"></div>
                                    </div>
                                </div>
                                <div class="stuffbox" id="namediv" style="">
                                    <h3><label>Pause On Mouse Over ?</label></h3>
                                    <div class="inside">
                                        <table>
                                            <tr>
                                                <td>
                                                    <input type="checkbox" id="pauseonmouseover" size="30" name="pauseonmouseover" value="" <?php if($settings['pauseonmouseover']==true){echo "checked='checked'";} ?> style="width:20px;">&nbsp;Pause On Mouse Over ? 
                                                    <div style="clear:both"></div>
                                                    <div></div>
                                                </td>
                                            </tr>
                                        </table>
                                        <div style="clear:both"></div>
                                    </div>
                                </div>
                           
                                <div class="stuffbox" id="namediv" style="">
                                    <h3><label>Image Height</label></h3>
                                    <div class="inside">
                                        <table>
                                            <tr>
                                                <td>
                                                    <input type="text" id="imageheight" size="30" name="imageheight" value="<?php echo $settings['imageheight']; ?>" style="width:100px;">
                                                    <div style="clear:both"></div>
                                                    <div></div>
                                                </td>
                                            </tr>
                                        </table>

                                        <div style="clear:both"></div>
                                    </div>
                                </div>
                                <div class="stuffbox" id="namediv" style="">
                                    <h3><label>Image Width</label></h3>
                                    <div class="inside">
                                        <table>
                                            <tr>
                                                <td>
                                                    <input type="text" id="imagewidth" size="30" name="imagewidth" value="<?php echo $settings['imagewidth']; ?>" style="width:100px;">
                                                    <div style="clear:both"></div>
                                                    <div></div>
                                                </td>
                                            </tr>
                                        </table>

                                        <div style="clear:both"></div>
                                    </div>
                                </div>
                                <div class="stuffbox" id="namediv" style="">
                                    <h3><label>Physically resize images ?</label></h3>
                                    <div class="inside">
                                        <table>
                                            <tr>
                                                <td>
                                                    <input style="width:20px;" type='radio' <?php if($settings['resizeImages']==1){echo "checked='checked'";}?>  name='resizeImages' value='1' >Yes &nbsp;<input style="width:20px;" type='radio' name='resizeImages' <?php if($settings['resizeImages']==0){echo "checked='checked'";} ?> value='0' >Resize using css
                                                    <div style="clear:both;padding-top:5px">If you choose "<b>Resize using css</b>" the quality will be good but some times large images takes time to load </div>
                                                    <div></div>
                                                </td>
                                            </tr>
                                        </table>
                                        <div style="clear:both"></div>
                                    </div>
                                </div>
                                <input type="submit"  name="btnsave" id="btnsave" value="Save Changes" class="button-primary">&nbsp;&nbsp;<input type="button" name="cancle" id="cancle" value="Cancel" class="button-primary" onclick="location.href='admin.php?page=vertical_thumbnail_slider_image_management'">
                                <?php wp_nonce_field('action_settings_add_edit','add_edit_nonce'); ?>
                            </form> 
                            <script type="text/javascript">

                                var $n = jQuery.noConflict();  
                                $n(document).ready(function() {

                                        $n("#scrollersettiings").validate({
                                                rules: {
                                                    isauto: {
                                                        required:true
                                                    },speed: {
                                                        required:true, 
                                                        number:true, 
                                                        maxlength:15
                                                    },
                                                    visible:{
                                                        required:true, 
                                                        number:true,
                                                        maxlength:15

                                                    },
                                                    scroll:{
                                                        required:true,
                                                        number:true,
                                                        maxlength:15  
                                                    },
                                                    scollerBackground:{
                                                        required:true,
                                                        maxlength:7  
                                                    },
                                                    /*scrollerwidth:{
                                                    required:true,
                                                    number:true,
                                                    maxlength:15    
                                                    },*/imageheight:{
                                                        required:true,
                                                        number:true,
                                                        maxlength:15    
                                                    },
                                                    imagewidth:{
                                                        required:true,
                                                        number:true,
                                                        maxlength:15    
                                                    }

                                                },
                                                errorClass: "image_error",
                                                errorPlacement: function(error, element) {
                                                    error.appendTo( element.next().next());
                                                } 


                                        })
                                        
                                         $n('#scollerBackground').wpColorPicker();
                                });

                            </script> 

                        </div>
                    </div>
                </div>  
            </div>      

            <div id="postbox-container-1" class="postbox-container" style="margin-top: 15px;"> 

                <div class="postbox"> 
                    <h3 class="hndle"><span></span>Access All Themes In One Price</h3> 
                    <div class="inside">
                        <center><a href="http://www.elegantthemes.com/affiliates/idevaffiliate.php?id=11715_0_1_10" target="_blank"><img border="0" src="<?php echo plugins_url( 'images/300x250.gif', __FILE__ );?>" width="250" height="250"></a></center>

                        <div style="margin:10px 5px">

                        </div>
                    </div>
                </div>
               

            </div>                                                 
            <div class="clear"></div>
        </div>
    </div>  
    <?php
    }        
    function vertical_thumbnail_image_management(){

        $uploads = wp_upload_dir();
        $baseDir=$uploads['basedir'];
        $baseDir=str_replace("\\","/",$baseDir);
        $pathToImagesFolder=$baseDir.'/wp-vertical-image-slider';
        
        $baseurl=$uploads['baseurl'];
        $baseurl.='/wp-vertical-image-slider/';
        
        $action='gridview';
        global $wpdb;


        if(isset($_GET['action']) and $_GET['action']!=''){


            $action=trim($_GET['action']);
        }

    ?>

    <?php 
        if(strtolower($action)==strtolower('gridview')){ 


            $wpcurrentdir=dirname(__FILE__);
            $wpcurrentdir=str_replace("\\","/",$wpcurrentdir);



        ?> 
        <!--[if !IE]><!-->
        <style type="text/css">

            @media only screen and (max-width: 800px) {

                /* Force table to not be like tables anymore */
                #no-more-tables table, 
                #no-more-tables thead, 
                #no-more-tables tbody, 
                #no-more-tables th, 
                #no-more-tables td, 
                #no-more-tables tr { 
                    display: block; 

                }

                /* Hide table headers (but not display: none;, for accessibility) */
                #no-more-tables thead tr { 
                    position: absolute;
                    top: -9999px;
                    left: -9999px;
                }

                #no-more-tables tr { border: 1px solid #ccc; }

                #no-more-tables td { 
                    /* Behave  like a "row" */
                    border: none;
                    border-bottom: 1px solid #eee; 
                    position: relative;
                    padding-left: 50%; 
                    white-space: normal;
                    text-align:left;      
                }

                #no-more-tables td:before { 
                    /* Now like a table header */
                    position: absolute;
                    /* Top/left values mimic padding */
                    top: 6px;
                    left: 6px;
                    width: 45%; 
                    padding-right: 10px; 
                    white-space: nowrap;
                    text-align:left;
                    font-weight: bold;
                }

                /*
                Label the data
                */
                #no-more-tables td:before { content: attr(data-title); }
            }
        </style>
        <!--<![endif]-->
        <style type="text/css">
            .pagination {
                clear:both;
                padding:20px 0;
                position:relative;
                font-size:11px;
                line-height:13px;
            }

            .pagination span, .pagination a {
                display:block;
                float:left;
                margin: 2px 2px 2px 0;
                padding:6px 9px 5px 9px;
                text-decoration:none;
                width:auto;
                color:#fff;
                background: #555;
            }

            .pagination a:hover{
                color:#fff;
                background: #3279BB;
            }

            .pagination .current{
                padding:6px 9px 5px 9px;
                background: #3279BB;
                color:#fff;
            }
        </style>  
        <div id="poststuff"  class="wrap">
            <div id="post-body" class="metabox-holder columns-2">
                <table><tr><td><a href="https://twitter.com/FreeAdsPost" class="twitter-follow-button" data-show-count="false" data-size="large" data-show-screen-name="false">Follow @FreeAdsPost</a>
                            <script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0];if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src="//platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document,"script","twitter-wjs");</script></td>
                        <td>
                            <a target="_blank" title="Donate" href="http://www.i13websolution.com/donate-wordpress_image_thumbnail.php">
                                <img id="help us for free plugin" height="30" width="90" src="<?php echo plugins_url( 'images/paypaldonate.jpg', __FILE__ );?>" border="0" alt="help us for free plugin" title="help us for free plugin">
                            </a>
                        </td>
                    </tr>
                </table>

                <?php 

                    $messages=get_option('vertical_thumbnail_slider_messages'); 
                    $type='';
                    $message='';
                    if(isset($messages['type']) and $messages['type']!=""){

                        $type=$messages['type'];
                        $message=$messages['message'];

                    }  


                    if($type=='err'){ echo "<div class='errMsg'>"; echo $message; echo "</div>";}
                    else if($type=='succ'){ echo "<div class='succMsg'>"; echo $message; echo "</div>";}


                    update_option('vertical_thumbnail_slider_messages', array());     
                ?>


                <div id="post-body-content" >  
                    <span><h3 style="color: blue;"><a target="_blank" href="http://www.i13websolution.com/wordpress-pro-plugins/wordpress-vertical-image-slider-pro-plugin.html">UPGRADE TO PRO VERSION</a></h3></span>

                    <div class="icon32 icon32-posts-post" id="icon-edit"><br></div>
                    <h2>Images <a class="button add-new-h2" href="admin.php?page=vertical_thumbnail_slider_image_management&action=addedit">Add New</a> </h2>
                    <form method="POST" action="admin.php?page=vertical_thumbnail_slider_image_management&action=deleteselected"  id="posts-filter">
                        <div class="alignleft actions">
                            <select name="action_upper" id="action_upper">
                                <option selected="selected" value="-1">Bulk Actions</option>
                                <option value="delete">delete</option>
                            </select>
                            <input type="submit" value="Apply" class="button-secondary action" id="deleteselected" name="deleteselected" onclick="return confirmDelete_bulk();">
                        </div>
                        <br class="clear">
                        <?php 

                            $settings=get_option('vertical_thumbnail_slider_settings'); 
                            $visibleImages=$settings['visible'];
                            $query="SELECT * FROM ".$wpdb->prefix."vertical_thumbnail_slider order by createdon desc";
                            $rows=$wpdb->get_results($query,'ARRAY_A');
                            $rowCount=sizeof($rows);

                        ?>
                        <?php if($rowCount<$visibleImages){ ?>
                            <h4 style="color: green"> Current slider setting - Total visible images <?php echo $visibleImages; ?></h4>
                            <h4 style="color: green">Please add atleast <?php echo $visibleImages; ?> images</h4>
                            <?php } else{
                                echo "<br/>";
                        }?>
                        <div id="no-more-tables">
                            <table cellspacing="0" id="gridTbl" class="table-bordered table-striped table-condensed cf" >
                                <thead>       
                                    <tr>
                                        <th class="manage-column column-cb check-column" scope="col"><input type="checkbox"></th>
                                        <th>Title</th>
                                        <th ><span></span></th>
                                        <th>Published On</th>
                                        <th>Edit</th>
                                        <th>Delete</th>
                                    </tr> 
                                </thead>

                                <tbody id="the-list">
                                    <?php

                                        if(count($rows) > 0){

                                            global $wp_rewrite;
                                            $rows_per_page = 5;

                                            $current = (isset($_GET['paged'])) ? ($_GET['paged']) : 1;
                                            $pagination_args = array(
                                                'base' => @add_query_arg('paged','%#%'),
                                                'format' => '',
                                                'total' => ceil(sizeof($rows)/$rows_per_page),
                                                'current' => $current,
                                                'show_all' => false,
                                                'type' => 'plain',
                                            );


                                            $start = ($current - 1) * $rows_per_page;
                                            $end = $start + $rows_per_page;
                                            $end = (sizeof($rows) < $end) ? sizeof($rows) : $end;

                                            for ($i=$start;$i < $end ;++$i ) {

                                                $delRecNonce=wp_create_nonce('delete_image');
                                                $row = $rows[$i];
                                                $id=$row['id'];
                                                $editlink="admin.php?page=vertical_thumbnail_slider_image_management&action=addedit&id=$id";
                                                $deletelink="admin.php?page=vertical_thumbnail_slider_image_management&action=delete&id=$id&nonce=$delRecNonce";
                                                $outputimgmain = $baseurl.$row['image_name']; 

                                            ?>   
                                            <tr valign="top">
                                                <td class="alignCenter check-column"   data-title="Select Record" ><input type="checkbox" value="<?php echo $row['id'] ?>" name="thumbnails[]"></td>
                                                <td   data-title="Title" ><strong><?php echo strip_tags($row['title']); ?></strong></td> 
                                                 <td  data-title="Image" class="alignCenter">
                                                    <img src="<?php echo $outputimgmain;?>" style="width:50px" height="50px"/>
                                                </td> 
                                                <td class="alignCenter"   data-title="Published On" ><?php echo $row['createdon']; ?></td>
                                                <td class="alignCenter"   data-title="Edit Record" ><strong><a href='<?php echo $editlink; ?>' title="edit">Edit</a></strong></td>  
                                                <td class="alignCenter"   data-title="Delete Record" ><strong><a href='<?php echo $deletelink; ?>' onclick="return confirmDelete();"  title="delete">Delete</a> </strong></td>  
                                            </tr>
                                            <?php 
                                            } 
                                        }
                                        else{
                                        ?>

                                        <tr valign="top" class="" id="">
                                            <td colspan="5" data-title="No Record" align="center"><strong>No Images Found</strong></td>  
                                        </tr>


                                        <?php 
                                        } 
                                    ?>      
                                </tbody>
                            </table>
                        </div>
                        <?php
                            if(sizeof($rows)>0){
                                echo "<div class='pagination' style='padding-top:10px'>";
                                echo paginate_links($pagination_args);
                                echo "</div>";
                            }
                        ?>
                        <br/>
                        <div class="alignleft actions" id="action_bottom">
                            <select name="action">
                                <option selected="selected" value="-1">Bulk Actions</option>
                                <option value="delete">delete</option>
                            </select>
                            <input type="submit" value="Apply" class="button-secondary action" id="deleteselected" name="deleteselected" onclick="return confirmDelete_bulk();">
                        </div>
                        <?php wp_nonce_field('action_settings_mass_delete','mass_delete_nonce'); ?>
                    </form>  
                    <script type="text/JavaScript">

                         function  confirmDelete_bulk(){
                            var topval=document.getElementById("action_bottom").value;
                            var bottomVal=document.getElementById("action_upper").value;
                       
                            if(topval=='delete' || bottomVal=='delete'){
                                
                            
                                var agree=confirm("Are you sure you want to delete selected images ?");
                                if (agree)
                                    return true ;
                                else
                                    return false;
                            }
                        }
                        function  confirmDelete(){
                            var agree=confirm("Are you sure you want to delete this image ?");
                            if (agree)
                                return true ;
                            else
                                return false;
                        }
                    </script>

                    <br class="clear">
                    <h3>To print this slider into WordPress Post/Page use below Short code</h3>
                    <input type="text" value="[print_vertical_thumbnail_slider]" style="width: 400px;height: 30px" onclick="this.focus();this.select()" />
                    <div class="clear"></div>
                    <h3>To print this slider into WordPress theme/template PHP files use below php code</h3>
                    <input type="text" value="echo do_shortcode('[print_vertical_thumbnail_slider]');" style="width: 400px;height: 30px" onclick="this.focus();this.select()" />

                    <div class="clear"></div> 
                </div>    
                <div id="postbox-container-1" class="postbox-container"> 
                    <div class="postbox"> 
                        <h3 class="hndle"><span></span>Recommended WordPress Themes</h3> 
                        <div class="inside">
                            <center><a href="http://www.elegantthemes.com/affiliates/idevaffiliate.php?id=11715_0_1_10" target="_blank"><img border="0" src="<?php echo plugins_url( 'images/300x250.gif', __FILE__ );?>" width="250" height="250"></a></center>

                            <div style="margin:10px 5px">

                            </div>
                        </div></div>
                    
                </div>    

                <div style="clear: both;"></div>
                <?php $url = plugin_dir_url(__FILE__);  ?>


            </div>
        </div>  
        <?php 
        }   
        else if(strtolower($action)==strtolower('addedit')){
            $url = plugin_dir_url(__FILE__);

        ?>
        <?php        
            if(isset($_POST['btnsave'])){

                  if ( !check_admin_referer( 'action_image_add_edit','add_edit_image_nonce')){
                      
                      wp_die('Security check fail'); 
                  }
                
                        
                //edit save
                if(isset($_POST['imageid'])){

                    //add new
                    $location='admin.php?page=vertical_thumbnail_slider_image_management';
                    $title=trim(htmlentities(strip_tags($_POST['imagetitle']),ENT_QUOTES));
                    $imageurl=trim(htmlentities(strip_tags($_POST['imageurl']),ENT_QUOTES));
                    $imageid=trim(htmlentities(strip_tags($_POST['imageid']),ENT_QUOTES));
                    $imagename="";
                     $imagename="";
                    if(trim($_POST['HdnMediaSelection'])!=''){

                        $postThumbnailID=(int)htmlentities(strip_tags($_POST['HdnMediaSelection']),ENT_QUOTES);
                        $photoMeta = wp_get_attachment_metadata( $postThumbnailID );
                        if(is_array($photoMeta) and isset($photoMeta['file'])) {

                            $fileName=$photoMeta['file'];
                            $phyPath=ABSPATH;
                            $phyPath=str_replace("\\","/",$phyPath);

                            $pathArray=pathinfo($fileName);

                            $imagename=$pathArray['basename'];

                            $upload_dir_n = wp_upload_dir(); 
                            $upload_dir_n=$upload_dir_n['baseurl'];
                            $fileUrl=$upload_dir_n.'/'.$fileName;
                            $fileUrl=str_replace("\\","/",$fileUrl);

                            $wpcurrentdir=dirname(__FILE__);
                            $wpcurrentdir=str_replace("\\","/",$wpcurrentdir);
                            $imageUploadTo=$pathToImagesFolder.'/'.$imagename;

                            @copy($fileUrl, $imageUploadTo);

                        }

                    }     



                    try{
                        if($imagename!=""){
                            $query = "update ".$wpdb->prefix."vertical_thumbnail_slider set title='$title',image_name='$imagename',
                            custom_link='$imageurl' where id=$imageid";
                        }
                        else{
                            $query = "update ".$wpdb->prefix."vertical_thumbnail_slider set title='$title',
                            custom_link='$imageurl' where id=$imageid";
                        } 
                        $wpdb->query($query); 

                        $vertical_thumbnail_slider_messages=array();
                        $vertical_thumbnail_slider_messages['type']='succ';
                        $vertical_thumbnail_slider_messages['message']='image updated successfully.';
                        update_option('vertical_thumbnail_slider_messages', $vertical_thumbnail_slider_messages);


                    }
                    catch(Exception $e){

                        $vertical_thumbnail_slider_messages=array();
                        $vertical_thumbnail_slider_messages['type']='err';
                        $vertical_thumbnail_slider_messages['message']='Error while updating image.';
                        update_option('vertical_thumbnail_slider_messages', $vertical_thumbnail_slider_messages);
                    }  


                    echo "<script type='text/javascript'> location.href='$location';</script>";
                    exit;
                }
                else{

                    //add new

                    $location='admin.php?page=vertical_thumbnail_slider_image_management';
                    $title=trim(htmlentities(strip_tags($_POST['imagetitle']),ENT_QUOTES));
                    
                    $imageurl=trim(htmlentities(strip_tags($_POST['imageurl']),ENT_QUOTES));
                    $createdOn=date('Y-m-d h:i:s'); 
                    if(function_exists('date_i18n')){

                        $createdOn=date_i18n('Y-m-d'.' '.get_option('time_format') ,false,false);
                        if(get_option('time_format')=='H:i')
                            $createdOn=date('Y-m-d H:i:s',strtotime($createdOn));
                        else   
                            $createdOn=date('Y-m-d h:i:s',strtotime($createdOn));

                    }

                   try{
                       
                         $location='admin.php?page=vertical_thumbnail_slider_image_management';

                         if(trim($_POST['HdnMediaSelection'])!=''){

                                $postThumbnailID=(int) htmlentities($_POST['HdnMediaSelection'],ENT_QUOTES);
                                $photoMeta = wp_get_attachment_metadata( $postThumbnailID );

                                if(is_array($photoMeta) and isset($photoMeta['file'])) {

                                    $fileName=$photoMeta['file'];
                                    $phyPath=ABSPATH;
                                    $phyPath=str_replace("\\","/",$phyPath);

                                    $pathArray=pathinfo($fileName);

                                    $imagename=$pathArray['basename'];

                                    $upload_dir_n = wp_upload_dir(); 
                                    $upload_dir_n=$upload_dir_n['baseurl'];
                                    $fileUrl=$upload_dir_n.'/'.$fileName;
                                    $fileUrl=str_replace("\\","/",$fileUrl);

                                    $wpcurrentdir=dirname(__FILE__);
                                    $wpcurrentdir=str_replace("\\","/",$wpcurrentdir);
                                    $imageUploadTo=$pathToImagesFolder.'/'.$imagename;

                                    @copy($fileUrl, $imageUploadTo);

                                }

                            }

                            $query = "INSERT INTO ".$wpdb->prefix."vertical_thumbnail_slider (title, image_name,createdon,custom_link) 
                            VALUES ('$title','$imagename','$createdOn','$imageurl')";

                            $wpdb->query($query); 

                            $vertical_thumbnail_slider_messages=array();
                            $vertical_thumbnail_slider_messages['type']='succ';
                            $vertical_thumbnail_slider_messages['message']='New image added successfully.';
                            update_option('vertical_thumbnail_slider_messages', $vertical_thumbnail_slider_messages);


                        }
                        catch(Exception $e){

                            $vertical_thumbnail_slider_messages=array();
                            $vertical_thumbnail_slider_messages['type']='err';
                            $vertical_thumbnail_slider_messages['message']='Error while adding image.';
                            update_option('vertical_thumbnail_slider_messages', $vertical_thumbnail_slider_messages);
                        }  

                    }     
                    echo "<script type='text/javascript'> location.href='$location';</script>";
                    exit;          

                } 

            
            else{ 

            ?>
            <div id="poststuff" >  
            <div id="post-body" class="metabox-holder columns-2"> 
                <div id="post-body-content">
                    <span><h3 style="color: blue;"><a target="_blank" href="http://www.i13websolution.com/wordpress-pro-plugins/wordpress-vertical-image-slider-pro-plugin.html">UPGRADE TO PRO VERSION</a></h3></span>
                    <div class="wrap">
                        <?php if(isset($_GET['id']) and $_GET['id']>0)
                            { 


                                $id= $_GET['id'];
                                $query="SELECT * FROM ".$wpdb->prefix."vertical_thumbnail_slider WHERE id=$id";
                                $myrow  = $wpdb->get_row($query);

                                if(is_object($myrow)){

                                    $title=  ($myrow->title);
                                    $image_link=($myrow->custom_link);
                                    $image_name=($myrow->image_name);

                                }   

                            ?>

                            <h2>Update Image </h2>

                            <?php }else{ 

                                $title='';
                                $image_link='';
                                $image_name='';

                            ?>
                            <h2>Add Image </h2>
                            <?php } ?>

                        <div id="poststuff">
                            <div id="post-body" class="metabox-holder columns-2">
                                <div id="post-body-content">
                                    <form method="post" action="" id="addimage" name="addimage" enctype="multipart/form-data" >

                                       <div class="stuffbox" id="namediv" style="">
                                        <h3><label for="link_name">Upload Image</label></h3>
                                        <div class="inside" id="fileuploaddiv">
                                            <?php if($image_name!=""){ ?>
                                                <div><b>Current Image : </b><a id="currImg" href="<?php echo $baseurl.$image_name; ?>" target="_new"><?php echo $image_name; ?></a></div>
                                                <?php } ?>      
                                            <div class="uploader">
                                                <br/>
                                               
                                                    <a  href="javascript:;" class="niks_media" id="myMediaUploader"><b>Click Here Uploader</b></a>
                                                    <input id="HdnMediaSelection" name="HdnMediaSelection" type="hidden" value="" />
                                                <br/>
                                            </div>  
                                                <script>

                                                    var $n = jQuery.noConflict();  
                                                    $n(document).ready(function() {
                                                            //uploading files variable
                                                            var custom_file_frame;
                                                            $n("#myMediaUploader").click(function(event) {
                                                                    event.preventDefault();
                                                                    //If the frame already exists, reopen it
                                                                    if (typeof(custom_file_frame)!=="undefined") {
                                                                        custom_file_frame.close();
                                                                    }

                                                                    //Create WP media frame.
                                                                    custom_file_frame = wp.media.frames.customHeader = wp.media({
                                                                            //Title of media manager frame
                                                                            title: "WP Media Uploader",
                                                                            library: {
                                                                                type: 'image'
                                                                            },
                                                                            button: {
                                                                                //Button text
                                                                                text: "Set Image"
                                                                            },
                                                                            //Do not allow multiple files, if you want multiple, set true
                                                                            multiple: false
                                                                    });

                                                                    //callback for selected image
                                                                    custom_file_frame.on('select', function() {

                                                                            var attachment = custom_file_frame.state().get('selection').first().toJSON();


                                                                            var validExtensions=new Array();
                                                                            validExtensions[0]='jpg';
                                                                            validExtensions[1]='jpeg';
                                                                            validExtensions[2]='png';
                                                                            validExtensions[3]='gif';
                                                                  
                                                                            var inarr=parseInt($n.inArray( attachment.subtype, validExtensions));

                                                                            if(inarr>0 && attachment.type.toLowerCase()=='image' ){

                                                                                var titleTouse="";
                                                                                var imageDescriptionTouse="";

                                                                                if($n.trim(attachment.title)!=''){

                                                                                    titleTouse=$n.trim(attachment.title); 
                                                                                }  
                                                                                else if($n.trim(attachment.caption)!=''){

                                                                                    titleTouse=$n.trim(attachment.caption);  
                                                                                }

                                                                                if($n.trim(attachment.description)!=''){

                                                                                    imageDescriptionTouse=$n.trim(attachment.description); 
                                                                                }  
                                                                                else if($n.trim(attachment.caption)!=''){

                                                                                    imageDescriptionTouse=$n.trim(attachment.caption);  
                                                                                }

                                                                                $n("#imagetitle").val(titleTouse);  
                                                                                $n("#image_description").val(imageDescriptionTouse);  

                                                                                if(attachment.id!=''){
                                                                                    $n("#HdnMediaSelection").val(attachment.id);  
                                                                                    $n("#err_daynamic").remove();
                                                                                }   

                                                                            }  
                                                                            else{

                                                                                alert('Invalid image selection.');
                                                                            }  
                                                                            
                                                                    });

                                                                    //Open modal
                                                                    custom_file_frame.open();
                                                            });
                                                    })
                                                </script>
                                                
                                        </div>
                                      </div>
                                        
                                        <div class="stuffbox" id="namediv" style="">
                                            <h3><label for="link_name">Image Title</label></h3>
                                            <div class="inside">
                                                <input type="text" id="imagetitle"  tabindex="1" size="30" name="imagetitle" value="<?php echo $title;?>">
                                                <div style="clear:both"></div>
                                                <div></div>
                                                <div style="clear:both"></div>
                                                <p><?php _e('Used in image alt for seo'); ?></p>
                                            </div>
                                        </div>
                                        <div class="stuffbox" id="namediv" style="">
                                            <h3><label for="link_name">Image Url(<?php _e('On click redirect to this url.'); ?>)</label></h3>
                                            <div class="inside">
                                                <input type="text" id="imageurl" class="url"  tabindex="1" size="30" name="imageurl" value="<?php echo $image_link; ?>">
                                                <div style="clear:both"></div>
                                                <div></div>
                                                <div style="clear:both"></div>
                                                <p><?php _e('On image click users will redirect to this url.'); ?></p>
                                            </div>
                                        </div>
                                        
                                        <?php if(isset($_GET['id']) and $_GET['id']>0){ ?> 
                                            <input type="hidden" name="imageid" id="imageid" value="<?php echo $_GET['id'];?>">
                                            <?php
                                            } 
                                        ?>
                                        <input type="submit" tabindex="4" onclick="return validateFile();" name="btnsave" id="btnsave" value="Save Changes" class="button-primary">&nbsp;&nbsp;<input type="button" name="cancle" id="cancle" value="Cancel" class="button-primary" tabindex="5" onclick="location.href='admin.php?page=vertical_thumbnail_slider_image_management'">
                                        <?php wp_nonce_field('action_image_add_edit','add_edit_image_nonce'); ?>
                                    </form> 
                                    <script type="text/javascript">

                                        var $n = jQuery.noConflict();  
                                        $n(document).ready(function() {

                                                $n("#addimage").validate({
                                                        rules: {
                                                            imagetitle: {
                                                                required:true, 
                                                                maxlength: 200
                                                            },imageurl: {
                                                                url:true,  
                                                                maxlength: 500
                                                            }
                                                        },
                                                        errorClass: "image_error",
                                                        errorPlacement: function(error, element) {
                                                            error.appendTo( element.next().next().next());
                                                        } 


                                                })
                                                
                                              
                                                  
                                        });

                                        function validateFile(){

                                        var $n = jQuery.noConflict();  
                                        if($n('#currImg').length>0 || $n.trim($n("#HdnMediaSelection").val())!="" ){
                                            return true;
                                        }
                                        else
                                            {
                                            $n("#err_daynamic").remove();
                                            $n("#myMediaUploader").after('<br/><label class="image_error" id="err_daynamic">Please select file.</label>');
                                            return false;  
                                        } 
                                            
                                    }
                                      
                                    </script> 

                                </div>
                            </div>
                        </div>  
                    </div>      
                </div>
                                                      
            </div>
            <?php 
            } 
        }  

        else if(strtolower($action)==strtolower('delete')){

            $retrieved_nonce = '';
            
            if(isset($_GET['nonce']) and $_GET['nonce']!=''){
              
                $retrieved_nonce=$_GET['nonce'];
                
            }
            if (!wp_verify_nonce($retrieved_nonce, 'delete_image' ) ){
        
                
                wp_die('Security check fail'); 
            }
                    
            $location='admin.php?page=vertical_thumbnail_slider_image_management';
            $deleteId=(int)$_GET['id'];

            try{


                $query="SELECT * FROM ".$wpdb->prefix."vertical_thumbnail_slider WHERE id=$deleteId";
                $myrow  = $wpdb->get_row($query);

                if(is_object($myrow)){

                    $image_name=  $myrow->image_name;
                    $wpcurrentdir=dirname(__FILE__);
                    $wpcurrentdir=str_replace("\\","/",$wpcurrentdir);
                    //$imagename=$_FILES["image_name"]["name"];
                    $imagetoDel=$pathToImagesFolder.'/'.$image_name;
                    @unlink($imagetoDel);

                    $query = "delete from  ".$wpdb->prefix."vertical_thumbnail_slider where id=$deleteId";
                    $wpdb->query($query); 

                    $vertical_thumbnail_slider_messages=array();
                    $vertical_thumbnail_slider_messages['type']='succ';
                    $vertical_thumbnail_slider_messages['message']='Image deleted successfully.';
                    update_option('vertical_thumbnail_slider_messages', $vertical_thumbnail_slider_messages);
                }    


            }
            catch(Exception $e){

                $vertical_thumbnail_slider_messages=array();
                $vertical_thumbnail_slider_messages['type']='err';
                $vertical_thumbnail_slider_messages['message']='Error while deleting image.';
                update_option('vertical_thumbnail_slider_messages', $vertical_thumbnail_slider_messages);
            }  

            echo "<script type='text/javascript'> location.href='$location';</script>";
            exit;

        }  
        else if(strtolower($action)==strtolower('deleteselected')){

            if(!check_admin_referer('action_settings_mass_delete','mass_delete_nonce')){
               
                wp_die('Security check fail'); 
            }
                    
            $location='admin.php?page=vertical_thumbnail_slider_image_management'; 
            if(isset($_POST) and isset($_POST['deleteselected']) and  ( $_POST['action']=='delete' or $_POST['action_upper']=='delete')){

                if(sizeof($_POST['thumbnails']) >0){

                    $deleteto=$_POST['thumbnails'];
                    $implode=implode(',',$deleteto);   

                    try{

                        foreach($deleteto as $img){ 

                            $query="SELECT * FROM ".$wpdb->prefix."vertical_thumbnail_slider WHERE id=$img";
                            $myrow  = $wpdb->get_row($query);

                            if(is_object($myrow)){

                                $image_name=$myrow->image_name;
                                $wpcurrentdir=dirname(__FILE__);
                                $wpcurrentdir=str_replace("\\","/",$wpcurrentdir);
                                //$imagename=$_FILES["image_name"]["name"];
                                $imagetoDel=$pathToImagesFolder.'/'.$image_name;
                                @unlink($imagetoDel);
                                $query = "delete from  ".$wpdb->prefix."vertical_thumbnail_slider where id=$img";
                                $wpdb->query($query); 

                                $vertical_thumbnail_slider_messages=array();
                                $vertical_thumbnail_slider_messages['type']='succ';
                                $vertical_thumbnail_slider_messages['message']='selected images deleted successfully.';
                                update_option('vertical_thumbnail_slider_messages', $vertical_thumbnail_slider_messages);
                            }

                        }

                    }
                    catch(Exception $e){

                        $vertical_thumbnail_slider_messages=array();
                        $vertical_thumbnail_slider_messages['type']='err';
                        $vertical_thumbnail_slider_messages['message']='Error while deleting image.';
                        update_option('vertical_thumbnail_slider_messages', $vertical_thumbnail_slider_messages);
                    }  

                    echo "<script type='text/javascript'> location.href='$location';</script>";
                    exit;


                }
                else{

                    echo "<script type='text/javascript'> location.href='$location';</script>"; 
                    exit;  
                }

            }
            else{

                echo "<script type='text/javascript'> location.href='$location';</script>";   
                exit;   
            }

        }      
    } 
    function verticalpreviewSliderAdmin(){
        $settings=get_option('vertical_thumbnail_slider_settings');
        
        $uploads = wp_upload_dir();
        $baseDir=$uploads['basedir'];
        $baseDir=str_replace("\\","/",$baseDir);
        $pathToImagesFolder=$baseDir.'/wp-vertical-image-slider';
        
        $baseurl=$uploads['baseurl'];
        $baseurl.='/wp-vertical-image-slider/';
        

    ?>      
    <div style="width: 100%;">  
        <div style="float:left;width:69%;">
            <div class="wrap">
                <h2>Slider Preview</h2>
                <br>
                <?php
                    $wpcurrentdir=dirname(__FILE__);
                    $wpcurrentdir=str_replace("\\","/",$wpcurrentdir);

                ?>
                <div id="poststuff">
                    <div id="post-body" class="metabox-holder columns-2">
                        <div id="post-body-content">
                            <div style="clear: both;"></div>
                            <?php $url = plugin_dir_url(__FILE__);  ?>
                            <div class="verticalmainTable"  style="background:<?php echo $settings['scollerBackground'];?>;display:inline-block">
                              
                                        <?php if($settings['auto']==false){?>
                                            <div class="uparrow">
                                                <img class="prev_vertical previmg" src="<?php echo plugin_dir_url(__FILE__);?>images/uparrow.png" />
                                            </div>
                                            <?php } ?>   
                                        <div class="verticalmainSliderDiv">
                                            <ul class="sliderUl">
                                                <?php
                                                    global $wpdb;
                                                    $imageheight=$settings['imageheight'];
                                                    $imagewidth=$settings['imagewidth'];
                                                    $query="SELECT * FROM ".$wpdb->prefix."vertical_thumbnail_slider order by createdon desc";
                                                    $rows=$wpdb->get_results($query,'ARRAY_A');

                                                    if(count($rows) > 0){
                                                        foreach($rows as $row){

                                                            $imagename=$row['image_name'];
                                                            $imageUploadTo=$pathToImagesFolder.'/'.$imagename;
                                                            $imageUploadTo=str_replace("\\","/",$imageUploadTo);
                                                            $pathinfo=pathinfo($imageUploadTo);
                                                            $filenamewithoutextension=$pathinfo['filename'];
                                                            $outputimg="";


                                                            if($settings['resizeImages']==0){

                                                                    $outputimg = $baseurl.$row['image_name']; 

                                                                }
                                                                else{
                                                                    
                                                                    $imagetoCheck=$pathToImagesFolder.'/'.$filenamewithoutextension.'_'.$imageheight.'_'.$imagewidth.'.'.$pathinfo['extension'];
                                                                    $imagetoCheckSmall=$pathToImagesFolder.'/'.$filenamewithoutextension.'_'.$imageheight.'_'.$imagewidth.'.'.strtolower($pathinfo['extension']);
                                            
                                                                    if(file_exists($imagetoCheck)){
                                                                        
                                                                        $outputimg = $baseurl.$filenamewithoutextension.'_'.$imageheight.'_'.$imagewidth.'.'.$pathinfo['extension'];
                                                                    }
                                                                    else if(file_exists($imagetoCheckSmall)){
                                                                        
                                                                        $outputimg = $baseurl.$filenamewithoutextension.'_'.$imageheight.'_'.$imagewidth.'.'.strtolower($pathinfo['extension']);
                                                                    }
                                                                    else{

                                                                        if(function_exists('wp_get_image_editor')){

                                                                            $image = wp_get_image_editor($pathToImagesFolder."/".$row['image_name']); 

                                                                            if ( ! is_wp_error( $image ) ) {
                                                                                $image->resize( $imagewidth, $imageheight, true );
                                                                                $image->save( $imagetoCheck );
                                                                                //$outputimg = plugin_dir_url(__FILE__)."imagestoscroll/".$filenamewithoutextension.'_'.$imageheight.'_'.$imagewidth.'.'.$pathinfo['extension'];
                                                                                
                                                                                 if(file_exists($imagetoCheck)){
                                                                                    $outputimg = $baseurl.$filenamewithoutextension.'_'.$imageheight.'_'.$imagewidth.'.'.$pathinfo['extension'];
                                                                                  }
                                                                                  else if(file_exists($imagetoCheckSmall)){
                                                                                      $outputimg = $baseurl.$filenamewithoutextension.'_'.$imageheight.'_'.$imagewidth.'.'.strtolower($pathinfo['extension']);
                                                                                  }
                                                                    
                                                                            }
                                                                            else{
                                                                               $outputimg = $baseurl.$row['image_name'];
                                                                            }     

                                                                        }
                                                                        else if(function_exists('image_resize')){

                                                                            $return=image_resize($pathToImagesFolder."/".$row['image_name'],$imagewidth,$imageheight) ;
                                                                            if ( ! is_wp_error( $return ) ) {

                                                                                $isrenamed=rename($return,$imagetoCheck);
                                                                                if($isrenamed){
                                                                                   // $outputimg = plugin_dir_url(__FILE__)."imagestoscroll/".$filenamewithoutextension.'_'.$imageheight.'_'.$imagewidth.'.'.$pathinfo['extension'];  
                                                                                    
                                                                                     if(file_exists($imagetoCheck)){
                                                                                         
                                                                                        $outputimg = $baseurl.$filenamewithoutextension.'_'.$imageheight.'_'.$imagewidth.'.'.$pathinfo['extension'];
                                                                                        
                                                                                       }
                                                                                        else if(file_exists($imagetoCheckSmall)){
                                                                                            
                                                                                            $outputimg = $baseurl.$filenamewithoutextension.'_'.$imageheight.'_'.$imagewidth.'.'.strtolower($pathinfo['extension']);
                                                                                            
                                                                                        }
                                                                            
                                                                            
                                                                                }
                                                                                else{
                                                                                    
                                                                                    $outputimg = $baseurl.$row['image_name']; 
                                                                                } 
                                                                            }
                                                                            else{
                                                                                
                                                                                $outputimg = $baseurl.$row['image_name']; 
                                                                            }  
                                                                        }
                                                                        else{

                                                                           $outputimg = $baseurl.$row['image_name']; 
                                                                        }  

                                                                        //$url = plugin_dir_url(__FILE__)."imagestoscroll/".$filenamewithoutextension.'_'.$imageheight.'_'.$imagewidth.'.'.$pathinfo['extension'];

                                                                    } 
                                                                }

                                                        ?>

                                                        <li class="sliderimgLiVertical" >
                                                            <?php if($settings['linkimage']==true){ ?> 
                                                                <a target="_blank" href="<?php if($row['custom_link']==""){echo '#';}else{echo $row['custom_link'];} ?>"><img src="<?php echo $outputimg; ?>" alt="<?php echo $row['title']; ?>" title="<?php echo $row['title']; ?>" style="width:<?php echo $settings['imagewidth']; ?>px;height:<?php echo $settings['imageheight']; ?>px"  /></a>
                                                                <?php }else{ ?>
                                                                <img src="<?php echo $outputimg;?>" alt="<?php echo $row['title']; ?>" title="<?php echo $row['title']; ?>" style="width:<?php echo $settings['imagewidth']; ?>px;height:<?php echo $settings['imageheight']; ?>px"  />
                                                                <?php } ?> 
                                                        </li>
                                                        <?php
                                                        }
                                                    }  
                                                ?>
                                            </ul>
                                        </div>
                                        <?php if($settings['auto']==false){?>
                                            <div class="downarrow">
                                                <img class="next_vertical nextimg" src="<?php echo plugin_dir_url(__FILE__);?>images/downarrow.png" />
                                            </div>
                                            <?php }?>  
                             <script type="text/javascript">
                                var $n = jQuery.noConflict();  
                                $n(document).ready(function() {


                                        $n(".verticalmainSliderDiv").jCarouselLite({
                                                btnNext: ".next_vertical",
                                                btnPrev: ".prev_vertical",
                                                <?php if($settings['auto']){?>
                                                    auto: <?php echo $settings['speed']; ?>,
                                                    <?php } ?>
                                                speed: <?php echo $settings['speed']; ?>,
                                                <?php if($settings['pauseonmouseover'] and $settings['auto']){ ?>
                                                    hoverPause: true,
                                                    <?php }else{ if($settings['auto']){?>   
                                                        hoverPause: false,
                                                        <?php }} ?>
                                                circular: <?php echo ($settings['circular'])? 'true':'false' ?>,
                                                <?php if($settings['visible']!=""){ ?>
                                                    visible: <?php echo $settings['visible'].','; ?>
                                                    <?php } ?>
                                                scroll: <?php echo $settings['scroll']; ?>,
                                                vertical:'true'

                                        });

                                        $n("#verticalmainscollertd").css("visibility","visible")


                                });
                            </script>              
                        </div>
                    </div>      
                </div>  
            </div>      
        </div>
        <div class="clear"></div>
    </div>
    </div>
    <div class="clear"></div>
    <h3>To print this slider into WordPress Post/Page use below Short code</h3>
    <input type="text" value="[print_vertical_thumbnail_slider]" style="width: 400px;height: 30px" onclick="this.focus();this.select()" />
    <div class="clear"></div>
    <h3>To print this slider into WordPress theme/template PHP files use below php code</h3>
    <input type="text" value="echo do_shortcode('[print_vertical_thumbnail_slider]');" style="width: 400px;height: 30px" onclick="this.focus();this.select()" />
    <div class="clear"></div>
    <?php       
    }

    function print_vertical_thumbnail_slider_func(){

        $wpcurrentdir=dirname(__FILE__);
        $wpcurrentdir=str_replace("\\","/",$wpcurrentdir);
        $settings=get_option('vertical_thumbnail_slider_settings');
        
        $uploads = wp_upload_dir();
        $baseDir=$uploads['basedir'];
        $baseDir=str_replace("\\","/",$baseDir);
        $pathToImagesFolder=$baseDir.'/wp-vertical-image-slider';
        
        $baseurl=$uploads['baseurl'];
        $baseurl.='/wp-vertical-image-slider/';
        
        ob_start();
    ?>      

    <div style="clear: both;"></div>
    <?php $url = plugin_dir_url(__FILE__);  ?>

    <div class="verticalmainTable"  style="background:<?php echo $settings['scollerBackground'];?>">
       
            <div id="verticalmainscollertd" style="background:<?php echo $settings['scollerBackground'];?>;display:inline-block">
                <?php if($settings['auto']==false){?>
                    <div class="uparrow">
                        <img class="prev_vertical previmg" src="<?php echo plugin_dir_url(__FILE__);?>images/uparrow.png" />
                    </div>
                    <?php } ?>   
               
                    <div class="verticalmainSliderDiv">
                        <ul class="sliderUl">
                            <?php
                                global $wpdb;
                                $imageheight=$settings['imageheight'];
                                $imagewidth=$settings['imagewidth'];
                                $query="SELECT * FROM ".$wpdb->prefix."vertical_thumbnail_slider order by createdon desc";
                                $rows=$wpdb->get_results($query,'ARRAY_A');

                                if(count($rows) > 0){
                                    foreach($rows as $row){

                                        $imagename=$row['image_name'];
                                        $imageUploadTo=$pathToImagesFolder.'/'.$imagename;
                                        $imageUploadTo=str_replace("\\","/",$imageUploadTo);
                                        $pathinfo=pathinfo($imageUploadTo);
                                        $filenamewithoutextension=$pathinfo['filename'];
                                        $outputimg="";


                                        if($settings['resizeImages']==0){

                                            $outputimg = $baseurl.$row['image_name']; 

                                        }
                                         else{
                            
                                                $imagetoCheck=$pathToImagesFolder.'/'.$filenamewithoutextension.'_'.$imageheight.'_'.$imagewidth.'.'.$pathinfo['extension'];
                                                $imagetoCheckSmall=$pathToImagesFolder.'/'.$filenamewithoutextension.'_'.$imageheight.'_'.$imagewidth.'.'.strtolower($pathinfo['extension']);


                                              if(file_exists($imagetoCheck)){
                                                  $outputimg = $baseurl.$filenamewithoutextension.'_'.$imageheight.'_'.$imagewidth.'.'.$pathinfo['extension'];

                                              }
                                              else if(file_exists($imagetoCheckSmall)){
                                                  $outputimg = $baseurl.$filenamewithoutextension.'_'.$imageheight.'_'.$imagewidth.'.'.strtolower($pathinfo['extension']);
                                              }
                                              else{


                                                  if(function_exists('wp_get_image_editor')){


                                                      $image = wp_get_image_editor($pathToImagesFolder."/".$row['image_name']); 
                                                      if ( ! is_wp_error( $image ) ) {
                                                          $image->resize( $imagewidth, $imageheight, true );
                                                          $image->save( $imagetoCheck );
                                                          //$outputimg = $baseurl.$filenamewithoutextension.'_'.$imageheight.'_'.$imagewidth.'.'.$pathinfo['extension'];

                                                          if(file_exists($imagetoCheck)){
                                                              $outputimg = $baseurl.$filenamewithoutextension.'_'.$imageheight.'_'.$imagewidth.'.'.$pathinfo['extension'];
                                                          }
                                                          else if(file_exists($imagetoCheckSmall)){
                                                              $outputimg = $baseurl.$filenamewithoutextension.'_'.$imageheight.'_'.$imagewidth.'.'.strtolower($pathinfo['extension']);
                                                          }
                                                      }
                                                      else{
                                                          $outputimg = $baseurl.$row['image_name'];
                                                      }     

                                                  }
                                                  else if(function_exists('image_resize')){

                                                      $return=image_resize($pathToImagesFolder."/".$row['image_name'],$imagewidth,$imageheight) ;
                                                      if ( ! is_wp_error( $return ) ) {

                                                          $isrenamed=rename($return,$imagetoCheck);
                                                          if($isrenamed){
                                                              //$outputimg = $baseurl.$filenamewithoutextension.'_'.$imageheight.'_'.$imagewidth.'.'.$pathinfo['extension'];  

                                                                if(file_exists($imagetoCheck)){
                                                                      $outputimg = $baseurl.$filenamewithoutextension.'_'.$imageheight.'_'.$imagewidth.'.'.$pathinfo['extension'];
                                                                  }
                                                                  else if(file_exists($imagetoCheckSmall)){
                                                                      $outputimg = $baseurl.$filenamewithoutextension.'_'.$imageheight.'_'.$imagewidth.'.'.strtolower($pathinfo['extension']);
                                                                  }


                                                          }
                                                          else{
                                                              $outputimg = $baseurl.$row['image_name']; 
                                                          } 
                                                      }
                                                      else{
                                                          $outputimg = $baseurl.$row['image_name'];
                                                      }  
                                                  }
                                                  else{

                                                      $outputimg = $baseurl.$row['image_name'];
                                                  }  

                                                  //$url = plugin_dir_url(__FILE__)."imagestoscroll/".$filenamewithoutextension.'_'.$imageheight.'_'.$imagewidth.'.'.$pathinfo['extension'];

                                              } 
                                          }

                                    ?>

                                    <li class="sliderimgLiVertical">
                                        <?php if($settings['linkimage']==true){ ?> 
                                            <a target="_blank" href="<?php if($row['custom_link']==""){echo '';}else{echo $row['custom_link'];} ?>"><img src="<?php echo $outputimg; ?>" alt="<?php echo $row['title']; ?>" title="<?php echo $row['title']; ?>" style="width:<?php echo $settings['imagewidth']; ?>px;height:<?php echo $settings['imageheight']; ?>px"  /></a>
                                            <?php }else{ ?>
                                            <img src="<?php echo $outputimg;?>" alt="<?php echo $row['title']; ?>" title="<?php echo $row['title']; ?>" style="width:<?php echo $settings['imagewidth']; ?>px;height:<?php echo $settings['imageheight']; ?>px"  />
                                            <?php } ?> 
                                    </li>
                                    <?php
                                    }
                                }  
                            ?>
                        </ul>
                    </div>
                <?php if($settings['auto']==false){?>
                    <div class="downarrow">
                        <img class="next_vertical nextimg" src="<?php echo plugin_dir_url(__FILE__);?>images/downarrow.png" />
                    </div>
                    <?php }?>  
            </div>  
         
    </div>

    <script type="text/javascript">
        var $n = jQuery.noConflict();  
        $n(document).ready(function() {


                $n(".verticalmainSliderDiv").jCarouselLite({
                        btnNext: ".next_vertical",
                        btnPrev: ".prev_vertical",
                        <?php if($settings['auto']){?>
                            auto: <?php echo $settings['speed']; ?>,
                            <?php } ?>
                        speed: <?php echo $settings['speed']; ?>,
                        <?php if($settings['pauseonmouseover'] and $settings['auto']){ ?>
                            hoverPause: true,
                            <?php }else{ if($settings['auto']){?>   
                                hoverPause: false,
                                <?php }} ?>
                        circular: <?php echo ($settings['circular'])? 'true':'false' ?>,
                        <?php if($settings['visible']!=""){ ?>
                            visible: <?php echo $settings['visible'].','; ?>
                            <?php } ?>
                        scroll: <?php echo $settings['scroll']; ?>,
                        vertical:'true'

                });

                $n("#verticalmainscollertd").css("visibility","visible")


        });
    </script>

    <?php
        $output = ob_get_clean();
        return $output;
    }
    
     function vertical_thumbnail_slider_get_wp_version() {

        global $wp_version;
        return $wp_version;
    }

    //also we will add an option function that will check for plugin admin page or not
    function vertical_thumbnail_slider_is_plugin_page() {

        $server_uri = "http://{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}";

        foreach (array('vertical_thumbnail_slider_image_management','vertical_thumbnail_slider') as $allowURI) {
            if(stristr($server_uri, $allowURI)) return true;
        }
        return false;
    }

    //add media WP scripts
    function vertical_thumbnail_slider_admin_scripts_init() {

        if(vertical_thumbnail_slider_is_plugin_page()) {
            //double check for WordPress version and function exists
            if(function_exists('wp_enqueue_media') && version_compare(vertical_thumbnail_slider_get_wp_version(), '3.5', '>=')) {
                //call for new media manager
                wp_enqueue_media();
            }
            wp_enqueue_style('media');
             wp_enqueue_style( 'wp-color-picker' );
            wp_enqueue_script( 'wp-color-picker' );
        }
    }
?>