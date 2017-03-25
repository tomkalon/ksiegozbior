function formUserBookChecker() {
    let displayValue = $('#user-addbook-form').css('display');
    if (displayValue == 'block') {
        $('#user-book').fadeOut(1);
    }
    else {
        if (document.getElementById('user-book')) {
            $('#user-book').fadeIn(200);
        }
    }
}

function addBookActiveChecker() {
    if ($('div').hasClass('errors')) {
        $('#user-addbook-form').fadeIn(100);
    }
}

function borrowTextChecker() {
    var borrowCheck = $('#form_borrowcheck:checked').length;
    if (borrowCheck) {
        $('#borrow-text').removeClass("hidden");
    }
    else {
        $('#borrow-text').addClass("hidden");
    }
}

function scrollToBook() {
    if (document.getElementById('js-book-id')) {
        let bookId = document.getElementById('js-book-id').textContent;
        let bookSelector = "tr[data-href='" + bookId + "']";
        let bookOffset = $(bookSelector).offset().top;
        $(bookSelector).addClass('show');
        let windowHeight = window.innerHeight / 2;
        $('body').scrollTop(bookOffset - windowHeight);
    }
}

function footerPositioner() {
    if (document.getElementById('logged')) {
        setTimeout(' document.location = "main"', 1000);
    }
    if (document.getElementById('registered')) {
        setTimeout(' document.location = "login"', 1500);
    }
    let windowHeight = window.innerHeight;
    let footerOffset = $('footer').offset().top;
    let footerHeight = $('footer').innerHeight();
    if (windowHeight > (footerOffset + footerHeight)) {
        $('footer').css({
            marginTop: (windowHeight - footerOffset - footerHeight + 20)
        , });
    };
}

function closeMessageWindow() {
    if (document.getElementById('user-messages')) {
        $('#user-messages').delay(1000).fadeOut(150);
    }
}

function goTop() {
    if (document.body.scrollTop > 100 || document.documentElement.scrollTop > 100) {
        if (!(scrollFlag)) {
            $('#go-top').fadeIn(500);
            scrollFlag = true;
        }
    }
    else {
        if (scrollFlag) {
            $('#go-top').fadeOut(500);
            scrollFlag = false;
        }
    }
}
$('#js-btn-addbook').click(function () {
    $('#user-addbook-form').fadeToggle(100);
    formUserBookChecker();
});
$('.js-close').click(function () {
    $(this).parent().parent().parent().fadeOut(100);
});
$('tr[data-href]').click(function () {
    document.location = 'page' + document.getElementById('page-number').textContent + '-show' + $(this).data('href');
});
$('#js-book-edit[data-href]').click(function () {
    document.location = 'page' + document.getElementById('page-number').textContent + '-show' + $(this).data('href') + '-edit';
});
$('#js-book-delete[data-href]').click(function () {
    document.location = 'page' + document.getElementById('page-number').textContent + '-show' + $(this).data('href') + '-delete';
});
$('#js-book-delete-conf[data-href]').click(function () {
    document.location = 'page' + document.getElementById('page-number').textContent + '-show' + $(this).data('href') + '-delete-conf';
});
$("input[type=checkbox]").click(function () {
    borrowTextChecker();
});
$("#go-top").click(function () {
    jQuery('html, body').animate({
        scrollTop: jQuery("body").offset().top
    }, 1000);
});
$("td.params img").hover(function () {
    let getAlt = $(this).attr('alt');
    $(this).parent().append($('<div class="tooltip">' + getAlt + '</div>'));
}, function () {
    $(this).parent().find("div:last").remove();
});
$(".status_img img").hover(function () {
    let getAlt = $(this).attr('alt');
    $(this).parent().append($('<div class="tooltip_window">' + getAlt + '</div>'));
}, function () {
    $(this).parent().find("div:last").remove();
});
let scrollFlag = true;
document.addEventListener("DOMContentLoaded", function () {
    borrowTextChecker();
    addBookActiveChecker();
    formUserBookChecker();
    footerPositioner();
    scrollToBook();
    closeMessageWindow();
    if (document.body.scrollTop > 100 || document.documentElement.scrollTop > 100) {
        scrollFlag = false;
    }
    goTop();
});
window.onscroll = function () {
    goTop();
};