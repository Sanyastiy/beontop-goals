
//Example for Contact Form 7

document.addEventListener('wpcf7mailsent', function sendMail(event) {
    if ('FORM_ID' == event.detail.contactFormId) {
        goalsModule.trigger('GOAL_NAME', 'GOAL_CATEGORY');
    }
}, false);

//Example for WPForms
//for exact WPForm
document.addEventListener('DOMContentLoaded', function (event) {
    var element = document.getElementById('wpforms-confirmation-57');
    if (element) {
        goalsModule.trigger('Email Feedback', 'Email');
    }
});

//for all WPForms
var container = document.querySelectorAll('.wpforms-container')[0];
var observer = new MutationObserver(function (mutations) {
    if (document.contains(container)) {
        var formSubmission = $('.wpforms-confirmation-container-full')[0];
        if (document.contains(formSubmission)) {
            goalsModule.trigger('Email Feedback', 'Email');
            observer.disconnect();
        }
    }
});
observer.observe(document, { attributes: true, childList: true, characterData: false, subtree: true });


//Example for direct link event

var body = document.querySelector('body');
body.addEventListener('click', function (event) {
    var target = event.target;
    if (target.tagName !== 'a') {
        target = target.closest('a');
        if (target == null) return;
    }

    if (target.href.includes('/contacts/')) goalsModule.trigger('Send Request', 'Clicks');

}, { passive: true });


