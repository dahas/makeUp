
$(document).ready(() => {

    $(document).on('show.bs.collapse', event => {
        if (event.target.id == "collapseOne") {
            Cookie.set("collapseOne_expanded", true)
        }
    })

    $(document).on('hide.bs.collapse', event => {
        if (event.target.id == "collapseOne") {
            Cookie.set("collapseOne_expanded", false)
        }
    })
});
