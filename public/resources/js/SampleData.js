
$(document).ready(() => {

    let locked = false;

    add = () => {
        $("#edit-form").hide();
        $("#add-form").fadeIn();
    }

    cancel = mode => {
        $("#"+mode+"-form").fadeOut(() => {
            $("#add-form input[type=text], textarea").val("");
        });
    }

    edit = (uid) => {
        $.ajax({
            type: 'GET',
            url: "/SampleData/getItem?uid=" + uid,
            dataType: 'json'
        }).done(data => {
            $("#add-form").hide();
            $("#edit-form input[name=uid]").val(data.uid)
            $("#edit-form input[name=year]").val(data.year)
            $("#edit-form input[name=name]").val(data.name)
            $("#edit-form input[name=city]").val(data.city)
            $("#edit-form input[name=country]").val(data.country)
            $("#edit-form").fadeIn();
        });
    }

    remove = (obj, uid) => {
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
                showToast("success", "Model '" + data.name + "' has been deleted.")
            });
        }
    }

    insert = () => {
        $.ajax({
            type: 'POST',
            url: "/SampleData/insert",
            data: $('form[name="add-sampledata"]').serialize(),
            dataType: 'json'
        }).done(data => {
            $('*[data-mod="list"]').prepend(data.rowHTML);
            $("tr#data-"+data.uid).fadeIn(); 
            showToast("success", "Model '" + data.name + "' has been added.");
            $("#add-form input[type=text], textarea").val("");
        });
    }

    update = () => {
        $.ajax({
            type: 'POST',
            url: "/SampleData/update",
            data: $('form[name="edit-sampledata"]').serialize(),
            dataType: 'json'
        }).done(data => {
            cancel("edit");
            refresh();
            showToast("success", "Model '" + data.name + "' has been updated.");
            $("#edit-form input[type=text], textarea").val("");
        });
    }

    refresh = () => {
        $('*[data-mod="list"]').animate({ opacity: 0 }, 250, () => {
            $.ajax({
                type: 'GET',
                url: "/SampleData/list"
            }).done(html => {
                $('*[data-mod="list"]').html(html);
                $('*[data-mod="list"]').animate({ opacity: 1 }, 250);
            });
        });
    }

});

