  //APIの読み込み
  var tag = document.createElement('script');
  tag.src = "https://www.youtube.com/iframe_api";
  var firstScriptTag = document.getElementsByTagName('script')[0];
  firstScriptTag.parentNode.insertBefore(tag, firstScriptTag);

  var video_id = 'GCZtPhDTM48';
  var lang_code = null;
  var temple_num = null;
  var movie_num = null;
  var end_flag = false;

  function set_movie_id(vid, lc = null, t_num = null, m_num = null) {
    video_id = vid;
    lang_code = lc;
    temple_num = t_num;
    movie_num = m_num;
  }

  var player;
  function onYouTubeIframeAPIReady() {
    player = new YT.Player('player', {
      height: '360',
      width: '640',
      videoId: video_id, // 動画IDを指定
      playerVars: {
        'rel': 0, // 関連動画を自チャンネルのみに制限
        'modestbranding': 1 // YouTubeロゴを控えめにする
      },
      events: {
        'onStateChange': onPlayerStateChange
      }
    });
  }

  // 状態が変化した時に呼ばれる関数
  function onPlayerStateChange(event) {
    if (event.data == YT.PlayerState.ENDED) {
      // 終了後の処理
      if (temple_num) {
        $.post("user.php", {
          md: "movie_end",
          lang_code : lang_code,
          temple_num: temple_num,
          movie_num: movie_num
        }, function(data){
          //alert("送信成功: " + data);
        });
      }
    }
  }
