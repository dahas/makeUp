
$(document).ready(() => {

    let locked = false;

    addItem = (obj, dataMod) => {
            obj.children[0].className = "fa-solid fa-spinner fa-spin-pulse";
            $.ajax({
                type: 'GET',
                url: "/SampleData/add"
            }).done(html => {
                $('*[data-mod="' + dataMod + '"]').html(html);
            });
    }

    deleteItem = (obj, uid) => {
        if (!locked) {
            locked = true;
            obj.children[0].className = "fa-solid fa-spinner fa-spin-pulse";
            $.ajax({
                type: 'GET',
                url: "/SampleData/delete?uid=" + uid,
                dataType: 'json'
            }).done(data => {
                $('#data-' + data.uid).fadeOut(() => {
                    locked = false;
                });
                showToast("success", "Item with ID " + data.uid + " has been deleted.")
            });
        }
    }

});

