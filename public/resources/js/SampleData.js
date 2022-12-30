
$(document).ready(() => {

    let locked = false;

    addItem = () => {
            $.ajax({
                type: 'GET',
                url: "/SampleData/add"
            }).done(html => {
                $('*[data-mod="SampleData"]').html(html);
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

    insert = () => {
            $.ajax({
                type: 'POST',
                url: "/SampleData/insert",
                data: $('form[name="sampledata"]').serialize()
            }).done(html => {
                $.ajax({
                    type: 'GET',
                    url: "/SampleData?json",
                    dataType: 'json'
                }).done(data => {
                    showToast("success", "Item with ID " + data.uid + " has been added.")
                    $('*[data-mod="SampleData"]').html(data.content);
                });
            });
    }

    refresh = () => {
            $.ajax({
                type: 'GET',
                url: "/SampleData/list"
            }).done(html => {
                $('*[data-mod="list"]').html(html);
            });
    }

});

