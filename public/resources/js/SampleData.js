
$(document).ready(() => {

    let locked = false;

    $('form[name="add-sampledata"]').on("submit", () => { insert(); return false; });
    $('form[name="edit-sampledata"]').on("submit", () => { update(); return false; });

    // Event handler must be re-attached after user logged in/out. We catch the event here.
    $(document).on("user.auth", () => {
        $('form[name="add-sampledata"]').on("submit", () => { insert(); return false; });
        $('form[name="edit-sampledata"]').on("submit", () => { update(); return false; });
    })

    add = () => {
        $("#edit-form").hide();
        $("#add-form").fadeIn();
    }

    cancel = mode => {
        $("#" + mode + "-form").fadeOut(() => {
            $("#add-form input[type=text], textarea").val("");
        });
    }

    edit = (uid) => {
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
        });
    }

    insert = () => {
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
        });
    }

    update = () => {
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
        });
    }

    remove = (obj, uid) => {
        $(obj).parent().parent().addClass('highlight');
        if (!locked) {
            locked = true;
            obj.children[0].className = "fa-solid fa-spinner fa-spin-pulse";
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
                    obj.children[0].className = "fa-solid fa-circle-xmark";
                    $(obj).parent().parent().removeClass('highlight');
                    showToast("error", "You must be logged in to delete a Topmodel!");
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

