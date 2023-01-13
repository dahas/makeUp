
$(document).ready(() => {

    let locked = false;

    add = () => {
        $('form[name="add-sampledata"]').on("submit", () => { 
            insert(); 
            return false; 
        });

        $("#edit-form").hide();
        $("#add-form").fadeIn();
    }

    cancel = mode => {
        $("#" + mode + "-form").fadeOut(() => {
            $("#add-form input[type=text], textarea").val("");
        });
    }

    edit = uid => {
        $('form[name="edit-sampledata"]').on("submit", () => { 
            update(); 
            return false; 
        });
        
        const initialIcon = $("#btn-edit-"+uid).html();
        $("#btn-edit-"+uid).html('<i class="fa-solid fa-spinner fa-spin-pulse"></i>');
        
        $.ajax({
            type: 'GET',
            url: "/SampleData/getItem?uid=" + uid,
            dataType: 'json'
        }).done(data => {
            if (data.authorized) {
                $("#add-form").hide();
                $("#edit-form input[name=uid]").val(data.uid)
                $("#edit-form input[name=year]").val(data.year)
                $("#edit-form input[name=name]").val(data.name)
                $("#edit-form input[name=city]").val(data.city)
                $("#edit-form input[name=country]").val(data.country)
                $("#edit-form").fadeIn();
            } else {
                showToast("error", "You must be logged in to modify the list of Topmodels!");
            }
            $("#btn-edit-"+uid).html(initialIcon);
        });
    }

    insert = () => {
        $("#btn-insert").html('<i class="fa-solid fa-spinner fa-spin-pulse"></i>');
        $.ajax({
            type: 'POST',
            url: "/SampleData/insert",
            data: $('form[name="add-sampledata"]').serialize(),
            dataType: 'json'
        }).done(data => {
            if (data.authorized) {
                $(data.rowHTML)
                    .hide()
                    .prependTo('*[data-mod="list"]')
                    .fadeIn()
                    .addClass('normal');
                showToast("success", "Model '" + data.name + "' has been added.");
                $("#add-form input[type=text], textarea").val("");
            } else {
                showToast("error", "You must be logged in to modify the list of Topmodels!");
            }
            $("#btn-insert").html('<i class="fa-solid fa-check"></i>');
        });
    }

    update = () => {
        $("#btn-update").html('<i class="fa-solid fa-spinner fa-spin-pulse"></i>');
        $.ajax({
            type: 'POST',
            url: "/SampleData/update",
            data: $('form[name="edit-sampledata"]').serialize(),
            dataType: 'json'
        }).done(data => {
            if (data.authorized) {
                cancel("edit");
                refresh();
                showToast("success", "Model '" + data.name + "' has been updated.");
                $("#edit-form input[type=text], textarea").val("");
            } else {
                showToast("error", "You must be logged in to modify the list of Topmodels!");
            }
            $("#btn-update").html('<i class="fa-solid fa-check"></i>');
        });
    }

    remove = uid => {
        if (!locked) {
            locked = true;
            $("#btn-del-"+uid).parent().parent().addClass('highlight');
            const initialIcon = $("#btn-del-"+uid).html();
            $("#btn-del-"+uid).html('<i class="fa-solid fa-spinner fa-spin-pulse"></i>');
            $.ajax({
                type: 'GET',
                url: "/SampleData/delete?uid=" + uid,
                dataType: 'json'
            }).done(data => {
                if (data.authorized) {
                    $('#data-' + data.uid).fadeOut(() => {
                        locked = false;
                    });
                    showToast("success", "Model '" + data.name + "' has been deleted.")
                } else {
                    locked = false;
                    $("#btn-del-"+uid).parent().parent().removeClass('highlight');
                    showToast("error", "You must be logged in to delete a Topmodel!");
                    $("#btn-del-"+uid).html(initialIcon);
                }
            });
        }
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

