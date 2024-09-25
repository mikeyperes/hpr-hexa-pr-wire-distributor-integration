<?php namespace hpr_distributor;

// Hook to load custom JavaScript in wp-admin head
add_action('admin_head', __NAMESPACE__ . '\\activate_listeners');

add_action('wp_ajax_modify_wp_config_constants',  __NAMESPACE__ . '\\modify_wp_config_constants_handler');    
add_action('wp_ajax_execute_function',  __NAMESPACE__ . '\\handle_execute_function_ajax');
add_action('wp_ajax_nopriv_execute_function',  __NAMESPACE__ . '\\handle_execute_function_ajax');  // For non-logged in users (optional)

function activate_listeners()
{?>
<script>
jQuery(document).ready(function($) {
    $('#dashboard-hpr-distributor .modify-wp-config').on('click', function(e) {
        e.preventDefault();

        const constant = $(this).data('constant');
        const value = $(this).data('value');
        const target = $(this).data('target');

        $.post(ajaxurl, {
            action: 'modify_wp_config_constants',
            constants: {
                [constant]: value
            }
        }, function(response) {
            if (response.success) {
                alert(response.data.message || 'Configuration updated successfully.');
                location.reload();
            } else {
                alert(response.data.message || 'Failed to update configuration.');
            }
        }).fail(function(jqXHR, textStatus, errorThrown) {
            console.error('AJAX Request Failed:', jqXHR, textStatus, errorThrown);
            alert('AJAX request failed: ' + textStatus + ', ' + errorThrown);
        });
    });
});
</script>
  <script type="text/javascript">
        jQuery(document).ready(function($) {
            // Event handler for enabling auto-updates for all plugins
            $('#dashboard-hpr-distributor #enable-plugin-auto-updates').on('click', function(e) {
                e.preventDefault();

                $.post(ajaxurl, {
                    action: 'enable_plugin_auto_updates'
                }, function(response) {
                    if (response.success) {
                        alert('Auto updates for all plugins have been enabled.');
                        location.reload();
                    } else {
                        alert('Failed to enable auto updates for plugins: ' + response.data.message);
                    }
                }).fail(function(jqXHR, textStatus, errorThrown) {
                    alert('AJAX request failed: ' + textStatus + ', ' + errorThrown);
                });
            });
        });
    </script>

    <script type="text/javascript">
        jQuery(document).ready(function($) {
            // Event handler for enabling WP Core auto-updates
            $('#dashboard-hpr-distributor #enable-auto-updates').on('click', function(e) {
                e.preventDefault();

                $.post(ajaxurl, {
                    action: 'modify_wp_config_constants',
                    constants: {
                        'WP_AUTO_UPDATE_CORE': 'true'
                    }
                }, function(response) {
                    if (response.success) {
                        alert('Auto updates have been enabled.');
                        location.reload();
                    } else {
                        alert('Failed to enable auto updates: ' + response.data.message);
                    }
                }).fail(function(jqXHR, textStatus, errorThrown) {
                    alert('AJAX request failed: ' + textStatus + ', ' + errorThrown);
                });
            });
        });
    </script>

    <script type="text/javascript">
        jQuery(document).ready(function($) {
            $('#dashboard-hpr-distributor .fix-ram-issue').on('click', function(e) {
                e.preventDefault();

                $.post(ajaxurl, {
                    action: 'modify_wp_config_constants',  // This needs to match the action hook in your PHP code
                    constants: {
                        'WP_MEMORY_LIMIT': '4000M' // Adding the constant to update
                    }
                }, function(response) {
                    console.log('Raw AJAX Response:', response); // Log the entire response
                    console.log('Data Object:', response.data);   // Log the data object to see what's inside

                    var message = response.data ? response.data.message : 'No message received';

                    if (response.success) {
                        alert(message);
                        location.reload();
                    } else {
                        alert(message);
                    }
                }).fail(function(jqXHR, textStatus, errorThrown) {
                    alert('AJAX request failed: ' + textStatus + ', ' + errorThrown);
                    console.error('AJAX Request Failed:', jqXHR, textStatus, errorThrown); // Debugging: Log the failure details
                });
            });
        });
    </script> 



<script>
$ = jQuery;
$(document).ready(function($) {
    // Handle click event and AJAX request all in one function
    $('#dashboard-hpr-distributor .execute-function').on('click', function() {

        var methodName = $(this).data('method');  // Get the method name
        var state = $(this).data('state');  // Get the state
        var setting = $(this).data('setting');  // Get the setting name
        var variable = $(this).data('variable');  // Get the setting name
        var slug = $(this).data('slug');  // Get the setting name
        var post_type = $(this).data('post_type');  // Get the setting name
        var name = $(this).data('name');  // Get the setting name

     



        // Ensure methodName and setting are available
        if (methodName) {
         //   console.log('State passed:', state);  // Log the state for debugging
        //    console.log('Setting passed:', setting);  // Log the setting for debugging

            // Make the AJAX call to execute the function
            var dataToSend = {
                action: 'execute_function',  // The action to hook into on the server-side
                method: methodName,          // Pass the method name
                setting: setting,            // Pass the setting name
                state: state,                // Pass the state
                variable: variable,                 // Pass the variable
                slug:slug,
                post_type:post_type,
                name:name
            };

            jQuery.ajax({
                url: ajaxurl,  // WordPress provides this for AJAX calls in the admin area
                type: 'post',
                data: dataToSend,
                success: function(response) {
                    if (response.success) {
                        alert(methodName+' executed successfully: ' + response.data);
                    } else {
                        alert('Error for '+methodName+': ' + response.data);
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    console.log('AJAX Error:', textStatus, errorThrown, jqXHR.responseText);
                    alert('An AJAX error occurred: ' + textStatus + ' - ' + errorThrown);
                }
            });
        } else {
            alert('No method or setting provided.');
        }
    });
});
</script>


<?php }

function modify_wp_config_constants_handler() {
    if (!current_user_can('manage_options')) {
        wp_send_json_error(['message' => 'Unauthorized']);
    }

    $constants = isset($_POST['constants']) ? $_POST['constants'] : [];
    if (empty($constants)) {
        wp_send_json_error(['message' => 'No constants provided']);
    }

    $result = modify_wp_config_constants($constants);

    if ($result['status']) {
        wp_send_json_success(['message' => $result['message']]);
    } else {
        wp_send_json_error(['message' => $result['message']]);
    }
}


function handle_execute_function_ajax() {
    // Verify if the method parameter is passed and is not empty
    if (isset($_POST['method']) && !empty($_POST['method'])) {
        $method_name = sanitize_text_field($_POST['method']);

        $variable = "";
        if(isset($_POST['variable']))
        $variable = $_POST['variable'];
        // Determine the correct namespace
        $namespace =  __NAMESPACE__ ."";
        $fully_qualified_function_name = $namespace . '\\' . $method_name;
        write_log("handle_execute_function_ajax - Method name passed: " . $fully_qualified_function_name, true);
        // Get the state if passed
        $state = isset($_POST['state']) ? sanitize_text_field($_POST['state']) : null;

        // Check if the function exists with the namespace
        if (function_exists($fully_qualified_function_name)) {
            // Execute the function with both the setting and state

            if($method_name == "toggle_php_ini_value")
            $response = call_user_func($fully_qualified_function_name,$variable, $state);
            else  if($method_name == "create_category_for_post_type")
          $response = call_user_func($fully_qualified_function_name,$_POST['name'],$_POST['slug'],$_POST['post_type'] );
else
            $response = call_user_func($fully_qualified_function_name, $state);
        
          
            // Send a success response with the result of the function execution
            wp_send_json_success($response);
        } else {
            write_log("The function does not exist: " . $fully_qualified_function_name, true);
            wp_send_json_error('The function does not exist.');
        }
    } else {
        wp_send_json_error('No method name provided.');
    }

    wp_die();  // This is required to properly terminate the script when doing AJAX in WordPress
}
?>