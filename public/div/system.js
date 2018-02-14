$(document).ready(function () 
{
  var SysCookie = new Cookie("__sys_makeup__");
  
  if (SysCookie.get("panel_open") == null)
    SysCookie.set("panel_open", true);
  
  /**
   * Show/hide the debug panel.
   */
  $("#dbg-handle").click(function () {
    if ($("#dbg-frame").is(":hidden")) {
      $("#dbg-frame").show(300);
      $("#dbg-handle i").attr("class", "fa fa-times");
      SysCookie.set("panel_open", true);
    } else {
      $("#dbg-frame").slideUp();
      $("#dbg-handle i").attr("class", "fa fa-chevron-left");
      SysCookie.set("panel_open", false);
    }
  });
});