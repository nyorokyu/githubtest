$(function() {
  /***** ローディング時のプログレスバー表示 *****/
  $(document).ready(function(){
    $('#area-progress').addClass('hide');
    $('#progress').empty();
  });
  function makeProgress() {
    $('#area-progress').removeClass('hide');

    var circle = new ProgressBar.Circle('#progress', {
        color: '#bd8f36',
        trailColor: '#f5f5f5',
        strokeWidth: 10,
        duration: 1500,
        easing: 'easeInOut'
    });

    circle.set(0.05);

    setTimeout(function() {
        circle.animate(0.2);
    }, 500);

    setTimeout(function() {
        circle.animate(0.4);
    }, 2000);

    setTimeout(function() {
        circle.animate(0.7);
    }, 5000);

    setTimeout(function() {
        circle.animate(0.9);
    }, 7000);

    setTimeout(function() {
        circle.animate(1);
    }, 10000);
  }

  /***** 戻るボタンの制御 *****/
  var isUnload = false; //unloadイベントの判断
  var isSameDomain = false; //前ページが同一ドメインか判断
  var ref = document.referrer;

  $(window).on('unload beforeunload', function() {
    isUnload = true;
  });
  var re = new RegExp(location.hostname, 'i');
  if(ref.match(re)) {
    isSameDomain = true;
  }

  $('.history-back').on('click', function() {
    var href = $(this).attr('href');
    if(isSameDomain) {
      history.back();
      setTimeout(function() {
        if(!isUnload) {
          location.href = href;
        }
      }, 100);
      return false;
    } else {
      location.href = href;
    }
  });

  //ログアウトの表示
  $('#area-dropdown').on('click', function() {
    var $slideArea = $(this).find('ul');
    if($($slideArea).hasClass('active')) {
      $($slideArea).slideUp(300);
      $($slideArea).removeClass('active');
    } else {
      $($slideArea).slideDown(300);
      $($slideArea).addClass('active');
    }

  });

  if($('.toggle').length) {
    $('.toggle').on('click', function() {
      var $openArea = '#' + $(this).data('toggle');
      if($($openArea).hasClass('active')) {
        $($openArea).removeClass('active');
        $($openArea).hide();
      } else {
        $($openArea).addClass('active');
        $($openArea).show();
      }
    });
  }

  // -----------------------------------------------------
  // 優良顧客比較の画面制御
  // -----------------------------------------------------
  var pathname = location.pathname;
  if(pathname.indexOf('excellent_comparison') != -1) {
    //セクション1の制御
    $('select[name="select_master"]').on('change', function() {
      var relationBtn = '.' + $(this).data('relation-btn');
      if($(relationBtn).prop('disabled')) {
        $(relationBtn).prop('disabled', false);
      }
    });

    // $('button[name="submit_m"]').on('click', function() {
    //   var relationBtn = '.' + $(this).data('relation-btn');
    //   if($(relationBtn).prop('disabled')) {
    //     $(relationBtn).prop('disabled', false);
    //   }
    // });

    //セクション2の制御
    $('button[name="submit_c"]').on('click', function() {
      var relationBtn = '.' + $(this).data('relation-btn');
      if($(relationBtn).prop('disabled')) {
        $(relationBtn).prop('disabled', false);
      }
    });

  }

  // -----------------------------------------------------
  // 見積作成依頼詳細の画面制御
  // -----------------------------------------------------
  if(pathname.indexOf('detail_quote') != -1) {
    $('input[name="agree"]').on('change', function() {
      if($(this).prop('checked')) {
        $('button[name="submit"]').prop('disabled', false);
      } else {
        $('button[name="submit"]').prop('disabled', true);
      }
    });
  }


  // -----------------------------------------------------
  // 数字カンマ区切り
  // -----------------------------------------------------
  function updateTextView(_obj) {
    var num = getNumber(_obj.val());
    if (num == 0) {
      _obj.val('');
    } else {
      _obj.val(num.toLocaleString());
    }
  }

  function getNumber(_str) {
    var arr = _str.split('');
    var out = new Array();
    for (var cnt=0; cnt<arr.length; cnt++) {
      if (isNaN(arr[cnt]) == false) {
        out.push(arr[cnt]);
      }
    }
    return Number(out.join(''));
  }

  function removeComma(_num) {
    var removed = _num.replace(/,/g, '');
    return parseInt(removed, 10);
  }

  // if (pathname.indexOf('make_quote') != -1) {
    // 自動カンマ区切り
    $('.number-separator').on('keyup', function() {
      updateTextView($(this));
    });
    // カンマ除去
    // $('button[name="submit"]').on('click', function() {
    //   document.getElementById('wage').value = removeComma(document.getElementById('wage').value);
    //   document.getElementById('parts').value = removeComma(document.getElementById('parts').value);
    //   document.getElementById('painting_wages').value = removeComma(document.getElementById('painting_wages').value);
    //   document.getElementById('painting_parts').value = removeComma(document.getElementById('painting_parts').value);
    // });
  // }


  // -----------------------------------------------------
  // データ取込時のプログレスバー表示
  // -----------------------------------------------------
  if(pathname.indexOf('insolvency_master_import') != -1) {
    $('button[name="import_m"]').on('click', function() {
      makeProgress();
    });
  }
  if(pathname.indexOf('insolvency_analysis') != -1) {
    $('button[name="import_c"]').on('click', function() {
      makeProgress();
    });
  }
  if(pathname.indexOf('excellent_comparison') != -1) {
    $('button[name="submit_m"]').on('click', function() {
      makeProgress();
    });
    $('button[name="submit_m_list"]').on('click', function() {
      makeProgress();
    });
    $('button[name="submit_c"]').on('click', function() {
      makeProgress();
    });
    $('button[name="submit_comparison"]').on('click', function() {
      makeProgress();
    });
  }
  // if(pathname.indexOf('detail_quote') != -1) {
  //   $('button[name="submit"]').on('click', function() {
      // var $form = $('form').serializeArray();
      // $.each($form, function(index, value) {
      //   if(!value.checkValidity()) {
      //     return false;
      //   }
      // });

      // if(!$('form')[0].checkValidity()) {
      //   makeProgress();
      // }
    // });
  // }

  if($('.area-change-content').length) {
    $('.area-change-content .btn-delete').on('click', function() {
      $(this).parents('.area-change-content').html(
        '<div class="box-flex">' +
          '<p>動画ファイル：</p>' +
          '<p class="flex2"><input type="file" name="movie_file"></p>' +
        '</div>'
      );
    });
  }

  // -----------------------------------------------------
  // TinyMCE投稿画像のwidth, heightの調整
  // -----------------------------------------------------
  //ユーザー画面
  if($('#area-content').length) {
    $('#area-content img').removeAttr('width height');
  }
  //管理画面
  if($('#text-editor').length) {
    //画面表示時にCSSを追加し、縮小する
    window.onload = function() {
      var tinymce = $('#text-editor_ifr');
      var $ifrmDoc = tinymce[0].contentWindow.document;

      $($ifrmDoc).find('img').each(function() {
        const WIDTH = 480;
        $(this).css({
          'width': WIDTH
        });
      });
    }

    //登録時、元が画像のサイズのまま保存する為、スタイルを除去
    $('button[name="submit"]').on('click', function() {
      var tinymce = $('#text-editor_ifr');
      var $ifrmDoc = tinymce[0].contentWindow.document;

      $($ifrmDoc).find('img').each(function() {
        $(this).css({
          'width': 'inherit'
        });
      });
    });
  }


});
