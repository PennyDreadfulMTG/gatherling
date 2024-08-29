<script type="text/javascript">

  var _gaq = _gaq || [];
<?php
  include_once 'lib.php';
global $CONFIG;
$account = '';
if (array_key_exists('analytics_account', $CONFIG)) {
    $account = $CONFIG['analytics_account'];
}
echo "_gaq.push(['_setAccount', '$account']);";
?>

  _gaq.push(['_trackPageview']);

  (function() {
    var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
    ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
  })();

</script>