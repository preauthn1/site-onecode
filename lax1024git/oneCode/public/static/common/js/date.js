window.onload = function() {
    new Jdate({
        el: '#date-group-1',
        format: 'YYYY-MM-DD',
        beginYear: 2018,
        endYear: 2100,
        init: function() {
            console.log('插件开始触发');
        },
        moveEnd: function() {
            console.log('滚动结束');
        },
        confirm: function(date) {
            console.log(date)
            console.log('确定按钮触发');
        },
        cancel: function() {
            console.log('插件运行取消');
        }
    })
    new Jdate({
        el: '#date-group-2',
        format: 'YYYY-MM-DD',
        beginYear: 2018,
        endYear: 2100,
        init: function() {
            console.log('插件开始触发');
        },
        moveEnd: function() {
            console.log('滚动结束');
        },
        confirm: function(date) {
            console.log(date)
            console.log('确定按钮触发');
        },
        cancel: function() {
            console.log('插件运行取消');
        }
    })
}