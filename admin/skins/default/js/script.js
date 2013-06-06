YUI().use('event-base', 'node', function (Y) {
    Y.on('domready', function () {
        Y.one('html').removeClass('no-js');
        Y.one('html').addClass('js');

        dashboard_menu_init();
    }); // end dom ready check
});

// add js enhancements to our dashboard menu
function dashboard_menu_init() {
    YUI({ classNamePrefix: 'pure' }).use('gallery-sm-menu', function (Y) {
        var dashboardMenu = new Y.Menu({
            container         : '#main-nav',
            sourceNode        : '#dashboard-menu',
            orientation       : 'vertical',
            hideOnOutsideClick: false,
            hideOnClick       : false
        });
        dashboardMenu.render();
        dashboardMenu.show();
    });
}
