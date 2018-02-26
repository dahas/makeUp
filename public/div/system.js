$(document).ready(function () 
{
  var SysCookie = new Cookie("__sys_makeup__");
  
  if (SysCookie.get("panel_open") == null)
    SysCookie.set("panel_open", true);
  
  /**
   * Show/hide the debug panel.
   */
  $("#dbg-handle").click(function () {
    console.log($("#dbg-frame").is(":hidden"));
    if ($("#dbg-frame").is(":hidden")) {
      $("#dbg-frame").show(300);
      $("#dbg-img").attr("src", "/div/img/close.png");
      SysCookie.set("panel_open", true);
    } else {
      $("#dbg-frame").slideUp();
      $("#dbg-img").attr("src", "/div/img/open.png");
      SysCookie.set("panel_open", false);
    }
  });
});