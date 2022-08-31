'use strict';

console.log('BeOnTop Goals Plugin');

/**
 * GOALS NAMING CONVENTION
 *
 * Category: 'Clicks'
 * Action: 'Click on Phone' - Клик на телефон
 * Action: 'Click on Email' - Клик на почту
 * Action: 'Click on WhatsApp' - Клик на WhatsApp
 * Action: 'Click on Telegram' - Клик на Telegram
 *
 * Category: Email
 * Action: 'Email Enquiry' - форма Book Now форму и аналогичные
 * Action: 'Email Feedback' - ФОС на странице контакты или в подвале
 * Action: 'Email Callback' - Заказ звонка
 * Action: 'Email Review' - Добавление отзыва
 * Action: 'Email Specialist' - Вызов специалиста
 * Action: 'Email Subscribe' - Подписаться на рассылку
 * Action: 'Email Career' - Отклик на вакансию
 *
 * Category: 'Ecommerce'
 * Action: 'View Product' - Просмотр продукта
 * Action: 'Add to Cart' - Добавление в корзину
 * Action: 'Remove from Cart' - Удаление из корзины
 * Action: 'Purchase' - Покупка
 * Action: 'Get Price List' - Получить прайс
 * Action: 'Enquire' - Отправить запрос
 * Action: 'Quick Order' - Заказ без корзины (купить в 1 клик)
 * Action: 'Review Product' - Отзыв к товару
 */

/**
 * Component for tracking goals
 *
 * Usage example:
 * goalsModule.trigger('goalName', 'goalCategory');
 *
 * @requires jquery
 * @type {{trigger}}
 */

var goalsModule = function () {
    // Enable/Disable tracking services
    var isMultilang = false;

    var goalDone = function goalDone(goalName, goalCategory) {
        if (isMultilang) {
            if (App.lang) goalName = goalName + ' ' + (App.lang[0].toUpperCase() + App.lang.slice(1)); else console.warn('У тега html отсутствует атрибут lang');
        }

        Object.keys(window).forEach(function (key) {
            if (key.includes('yaCounter')) window[key].reachGoal(goalName, function () { });
        });

        if (typeof ga !== 'undefined') window.ga('send', 'event', goalCategory, goalName);
        if (typeof gtag !== 'undefined') window.gtag('event', goalName, { event_category: goalCategory });
        if (typeof fbq !== 'undefined') window.fbq('track', goalName, {});
        console.log('Goal done. name: ' + goalName + ', category: ' + goalCategory);
    };

    var body = document.querySelector('body');

    body.addEventListener('click', function (event) {
        var target = event.target;
        var targetButton = event.target;

        if (target.tagName !== 'a') {
            target = target.closest('a');
            if (target == null) return;
        }

        if (target.href.includes('tel:') && window.innerWidth < 1000) goalDone('Click on Phone', 'Clicks');
        if (target.href.includes('mailto:')) goalDone('Click on Email', 'Clicks');

        if (target.href.includes('https://www.facebook.com/')) goalDone('Click on FaceBook', 'Clicks');
        if (target.href.includes('https://api.whatsapp.com/') || target.href.includes('https://wa.me/')) goalDone('Click on WhatsApp', 'Clicks');
        if (target.href.includes('tg:')) goalDone('Click on Telegram', 'Clicks');
        if (target.href.includes('linkedin.com/company')) goalDone('Click on LinkedIn', 'Clicks');
        if (target.href.includes('instagram.com')) goalDone('Click on Instagram', 'Clicks');

        if (target.href.includes('/checkout/')) goalDone('Place Order', 'Ecommerce');
        if (target.href.includes('?action=yith-woocompare-add-product')) goalDone('Add to Compare', 'Ecommerce');
        if (window.location.href.includes('order-received')) goalDone('Purchase', 'Ecommerce');

        if (targetButton.tagName !== 'button') {
            targetButton = targetButton.closest('button');
            if (targetButton == null) return;
        }

        if (targetButton.name.includes('add-to-cart')) goalDone('Add to Cart', 'Ecommerce');

    }, { passive: true });

    //Tawk form integration added
    if (typeof (Tawk_API) != "undefined") {
        Tawk_API = Tawk_API || {};
        Tawk_API.onOfflineSubmit = function (data) {
            goalDone('Tawk Form', 'Email');
        };
    }

    return Object.freeze({
        trigger: function trigger(name, params) {
            goalDone(name, params);
        }
    });
}();
