
$(document).ready(() => {

    let fadeDurMS = 200;

    getAttachedParameter = paramter => {
        let params = $('#mup-router').attr('src').split("?").pop().split("&");
        let paramsArr = [];
        for (let j = 0; j < params.length; j++) {
            let keyVal = params[j].split("=");
            let key = keyVal[0];
            let val = keyVal[1];
            if (key == paramter) {
                return val;
            }
            paramsArr[key] = val[1];
        }
        return paramsArr;
    }

    let rewriting = getAttachedParameter("rw");

    setRoute = (mod, uri) => {
        if (mod) {
            $(this).blur();
            $('nav li a.active').removeClass('active');
            let state = { path: uri, segment: { dataMod: 'content', html: '' }, title: '', content: '' };
            loadContent(state)
            history.pushState(state, mod, uri);
        }
    }

    loadContent = async state => {
        if (!state) {
            let data = await requestData(rewriting == 1 ? 'index.html' : '?mod=index');
            $('*[data-mod="' + data.segment.dataMod + '"]').html(data.segment.html);
            if (data.content) {
                $('*[data-mod="content"]').html(data.content);
            }
            $(document).prop('title', data.title);
        } else if (state.segment.html == '') {
            let data = await requestData(state.path);
            $('*[data-mod="' + data.segment.dataMod + '"]').html(data.segment.html);
            if (data.content) {
                $('*[data-mod="content"]').html(data.content);
            }
            $(document).prop('title', data.title);
        } else {
            $('*[data-mod="content"]').animate({ opacity: 0 }, fadeDurMS, () => {
                $('*[data-mod="' + state.segment.dataMod + '"]').html(state.segment.html);
                if (state.content) {
                    $('*[data-mod="content"]').html(state.content);
                }
                $(document).prop('title', state.title);
                $('*[data-mod="content"]').animate({ opacity: 1 }, fadeDurMS);
            });
        }
    }

    requestData = async path => {
        $('*[data-mod="content"]').animate({ opacity: 0 }, fadeDurMS);
        let state = {};
        await $.ajax({
            type: 'GET',
            url: rewriting == 1 ? '/json/' + path : path + '&render=json',
            dataType: 'json'
        }).fail(() => {
            state = {
                path: path,
                segments: [],
                title: "Error!",
                content: "Sorry! Something has gone wrong :("
            };
        }).done(data => {
            state = { path: path, segment: data.segment, title: data.title, content: data.content };
            history.replaceState(state, data.module, path);
            if (data.content) {
                $('*[data-mod="content"]').html(data.content);
            }
            $('*[data-mod="content"]').animate({ opacity: 1 }, fadeDurMS);
        });
        return state;
    }

    window.onpopstate = event => {
        loadContent(event.state);
    }

});

