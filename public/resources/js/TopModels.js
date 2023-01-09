
$(document).ready(() => {

    /*****************************************************************************\
    | IMPORTANT NOTE: The events of the Bootstrap collapsible must be registered  |
    | on document level because elements are asynchronously inserted and removed  |
    | from the DOM.                                                               |
    \*****************************************************************************/

    $(document).on('show.bs.collapse', event => {
        if (event.target.id == "collapseOne") {
            console.log("EXPANDING");
            Cookie.set("collapseOne_expanded", true)
        }
    })

    $(document).on('hide.bs.collapse', event => {
        if (event.target.id == "collapseOne") {
            console.log("COLLAPSING");
            Cookie.set("collapseOne_expanded", false)
        }
    })
});
