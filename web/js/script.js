$('.js-close').click(function () {
    $(this).parent().parent().parent().fadeOut(100);
});
// redirects
//---to book list
$('.js-user-books').click(function () {
    document.location = 'page1-show';
});
//---to book item
$('tr[data-href]').click(function () {
    document.location = 'page' + document.getElementById('page-number').textContent + '-show' + $(this).data('href');
});
//---to book edit
$('#js-book-edit[data-href]').click(function () {
    document.location = 'page' + document.getElementById('page-number').textContent + '-show' + $(this).data('href') + '-edit';
});
//---to book delete ask confirm
$('#js-book-delete[data-href]').click(function () {
    document.location = 'page' + document.getElementById('page-number').textContent + '-show' + $(this).data('href') + '-delete';
});
//---to book delete execution
$('#js-book-delete-conf[data-href]').click(function () {
    document.location = 'page' + document.getElementById('page-number').textContent + '-show' + $(this).data('href') + '-delete-conf';
});
// checkbox active checker
$("input[type=checkbox]").click(function () {
    borrowTextChecker();
});
// tooltips
//--- status tooltip
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
//--- info tooltip
$("#display_number").after('<div id="display_info"></div>');
$("#display_info").hover(function () {
    $(this).append($('<div class="display_tooltip">Tylko wartości od 25 do 200 mogą zostać zapisane do ustawień profilu na stałe. Pozostałe są aktualne do końca sesji.</div>'))
}, function () {
    $(this).parent().find("div:last").remove();
});
// lightbox
jQuery('.resize').click(function () {
    jQuery('#js-piobox').addClass('box-bg');
    let windowHeight = window.innerHeight;
    let windowWidth = window.innerWidth;
    let imgMaxHeight = windowHeight - 80;
    let imgMaxWidth = windowWidth - 80;
    image = jQuery(this).clone();
    jQuery(image).removeClass('resize');
    jQuery(image).addClass('box-border');
    jQuery(image).css('maxHeight', imgMaxHeight);
    jQuery(image).css('maxWidth', imgMaxWidth);
    jQuery('#js-piobox-img').append(image);
    let imgHeight = jQuery(image).height();
    let imgCalc;
    let imgTop;
    if (windowHeight > imgHeight) {
        imgCalc = ((windowHeight - imgHeight) / 2) - 15;
        imgTop = '+=' + imgCalc;
    }
    else {
        imgTop = '+=30';
    }
    jQuery(image).css('top', imgTop);
});
jQuery('#js-piobox').click(function () {
    jQuery('#js-piobox').toggleClass('box-bg');
    jQuery('.box-border').detach();
});
// pagination
function paginationNav() {
    actualPage = document.getElementById('page-number').textContent;
    allPage = document.getElementById('page-all').textContent;
    if (actualPage <= 1) {
        $('.left').addClass('btn-muted');
    }
    else {
        $(".left").click(function () {
            document.location = 'page' + (--actualPage) + '-show';
        });
    }
    if (actualPage >= allPage) {
        $('.right').addClass('btn-muted');
    }
    else {
        $(".right").click(function () {
            document.location = 'page' + (++actualPage) + '-show';
        });
    }
    // go to top btn
    $("#go-top").click(function () {
        jQuery('html, body').animate({
            scrollTop: jQuery("body").offset().top
        }, 1000);
    });
}
// window responsive positioner
function windowPositioner() {
    let windowHeight = window.innerHeight;
    let elementHeight = $('.window-container').height();
    if ((windowHeight - 150) < elementHeight) {
        $('.window-container').addClass('windowcontainer-mod');
        $('.window').addClass('window-mod');
        console.log(windowHeight);
        console.log(elementHeight);
    }
}

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

function loginRedirect() {
    if (document.getElementById('logged')) {
        setTimeout(' document.location = "main"', 1000);
    }
    if (document.getElementById('registered')) {
        setTimeout(' document.location = "login"', 1500);
    }
}
let scrollFlag = true;
document.addEventListener("DOMContentLoaded", function () {
    borrowTextChecker();
    addBookActiveChecker();
    formUserBookChecker();
    footerPositioner();
    scrollToBook();
    closeMessageWindow();
    windowPositioner();
    loginRedirect();
    paginationNav();
    if (document.body.scrollTop > 100 || document.documentElement.scrollTop > 100) {
        scrollFlag = false;
    }
    goTop();
});
window.onscroll = function () {
    goTop();
};