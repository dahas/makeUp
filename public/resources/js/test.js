
$(document).ready(() => {

    let locked = false;

    deleteItem = (obj, uid) => {
        if (!locked) {
            locked = true;
            obj.children[0].className = "fa-solid fa-spinner fa-spin-pulse";
            $.ajax({
                type: 'GET',
                url: "/test?task=delete&uid=" + uid,
                dataType: 'json'
            }).done(data => {
                console.log(data.success);
                $('#data-' + data.uid).fadeOut(() => {
                    locked = false;
                });
                showToast("success", "Item with ID " + data.uid + " has been deleted.")
            });
        }
    }

});

