$.fn.ready(function() {
    var download_status = false;

    var download_init = function() {
        download_status = true;
        swal({
            title: "导出文件进度",
            text: $('#tpl-download-process').html(),
            html: true,
            confirmButtonColor: "#2196F3",
            confirmButtonText: "关闭",
        },
        function(){
            download_status = false;
        });

        var $form = $(this).data('export-form'); // your form
        var $url = $(this).data('export-url'); // your path url

        start_download_queue($form, $url, 0);
    };

    var start_download_queue = function(f, u, p) {
        if (download_status == false) {
            return;
        }
        if (p == 0) {
            $.ajax({
                url: u + '?export=1',
                data: $('#' + f).serialize(),
                type: 'get',
                success: function(xhr) {
                    if (xhr.completeSize < 100) {
                        download_update_process(xhr.completeSize);
                        start_download_queue(f, xhr.url,p + 1);
                    } else {
                        download_update_process(xhr.completeSize);
                        download_complete_success(xhr);
                    }
                },
                error: function() {
                    alert('导出失败');
                }
            });
        } else {
            $.ajax({
                url: u,
                success: function(xhr) {
                    if (xhr.completeSize < 100) {
                        download_update_process(xhr.completeSize);
                        start_download_queue(f, xhr.url, p + 1);
                    } else {
                        download_update_process(xhr.completeSize);
                        download_complete_success(xhr);
                    }
                },
                error: function() {
                    alert('导出失败');
                }
            });
        }

    }

    var download_update_process = function(num) {
        var $pb = $('#h-fill-basic .progress-bar');
        $pb.attr('data-transitiongoal', num);
        $pb.progressbar({
            display_text: 'fill'
        });
    }

    var download_complete_success = function(xhr) {
        if (xhr.code == 200) {
            $('#monitor_download_file-btnIconEl').removeClass('hidden');
            $('#monitor_download_file-btnIconEl').on('click', function() {
                download_execute(xhr.fileName);
            });
            download_execute(xhr.fileName);
        } else {
            alert('导出数据异常，执行过程终止.');
        }
    }

    var download_execute = function(fileName) {
        window.location.href = '/internal/api/download_execute?export_name=' + fileName;
    }

    $("[data-plugin=exprot-file]").on('click', download_init);
});
