<?php

// Variables that are used are:
// Mode: Used to select between send and read modes
// Type: Type of message being sent. Options are: Bug Report and Update Request

session_start();
include 'lib.php';

print_header("Message");

?>

<div class="grid_10 suffix_1 prefix_1">
<div id="gatherling_main" class="box">

<?php    

if (Player::isLoggedIn()) 
    {message_content();}
else 
    {linkToLogin();}

function message_content() {
if(isset($_POST['submit'])) {submit_message();}
if(isset($_GET['mode'])) {$mode = $_GET['mode'];} else {$mode = "";}

if (strcmp($mode, "Send") == 0) {message_form();}

} // message_content

function message_form() {
if(isset($_GET['mode'])) {$mode = $_GET['mode'];} else {$mode = "";}
if(isset($_GET['type'])) {$msg_type = $_GET['type'];} else {$msg_type = "";}

?>

<div class="uppertitle"> Message Center: <?php echo $mode . " "; echo $msg_type; ?> </div>

<form method="post" action="message.php">
    <table>
        <tr>
            <td><label for="mtgoid">MTGO ID:</label></td>
            <td><input type="text" id="mtgoid" name="mtgoid" size="32" /></td>
        </tr>
        <tr>
            <td><label for="email">Email Address:</label></td>
            <td><input type="text" id="email" name="email" size="32" /></td>
        </tr>
        <tr>
            <td><label for="content">Message:</label></td>
            <td><textarea id="content" rows="20" cols="60" name="content"></textarea></td>
        </tr>
        <tr><td align="center" colspan="2"><input type="submit" class="inputbutton" value="Send <?php echo $msg_type ?>" name="submit" /></td></tr>
    </table>
</form>

<?php
} // message_form;

function submit_message() {
    // collect message information
    $mtgo_id  = $_POST['mtgoid'];
    $email    = $_POST['email'];
    $content  = $_POST['content'];
    $msg_type = str_replace('Send ','',$_POST['submit']); // grabbing the type of message
    
    // Create and output confirmation message to user
    echo '<div class="uppertitle">' . $msg_type . ' Submission</div>';
    echo '<p><br />Thanks for submitting a ' . $msg_type . ' form ' . $mtgo_id . '<br />';
    echo 'We will respond to you about your ' . $msg_type . ' at ' . $email . '<br /><br />';
    echo 'Here was the contents of your message: <br />';
    echo $content . '<br /><br />';

    // prepare and send data in an email to myself
    $msg = "*** This is a copy of the $msg_type that $mtgo_id submitted to Gatherling.com\n" .
            "$mtgo_id's email address is: $email.\n" .
            "Here is the $msg_type: \n\n$content";
    $subject = $msg_type;
    $bcc = "allthegoodnamesaretaken@ymail.com";
    $from = "Gatherling.com <no-reply@Gatherling.com>";
    
    $accepted_for_delivery = mail($email, $subject, $msg, 'From:' . $from . "\r\nBcc:" . $bcc);
    if ($accepted_for_delivery)
        echo 'Message was accepted by the server for delivery.</p><br />';
    else
        echo 'Error: Message was not accepted by the server for delivery.</p><br />';
}
?>

</div> <!-- gatherling_main -->
</div> <!-- grid_10 suffix_1 prefix_1 -->

<?php print_footer(); ?> 