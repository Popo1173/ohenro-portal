
  //APIの読み込み
  let tag = document.createElement('script');
  tag.src = "https://www.youtube.com/iframe_api";
  let firstScriptTag = document.getElementsByTagName('script')[0];
  firstScriptTag.parentNode.insertBefore(tag, firstScriptTag);

  let lang_code = null;
  let temple_num = null;
  let movie_num = null;
  let end_flag = false;

  function set_movie_id(lang = null, temple = null, movie = null) {
    lang_code = lang;
    temple_num = temple;
    movie_num = movie;
  }

  let player;
  function onYouTubeIframeAPIReady() {
    var playerElement = document.getElementById('player');
    var video_id = playerElement.getAttribute('data-video-id');

    player = new YT.Player('player', {
      height: '360',
      width: '640',
      videoId: video_id, // 動画IDを指定
      playerVars: {
        'rel': 0, // 関連動画を自チャンネルのみに制限
        'modestbranding': 1 // YouTubeロゴを控えめにする
      }
    });
  }

  //動画エリアクリック時
  document.getElementById('youtube').addEventListener('click', (e) => {
    if (player) {
      // クリックされた座標にある全要素を取得
      const elements = document.elementsFromPoint(e.clientX, e.clientY);

      // 重なっている上部要素を除外して、下の要素を見つける
      const clickedElement = elements.find(el => el.classList.contains('overlay'));

      if (clickedElement) {
        //Mute、UnMute
        if (player.isMuted() == 1) {
          player.unMute();
          document.getElementById('message-overlay').innerHTML = 'Mute';
        }
        else {
          player.mute();
          document.getElementById('message-overlay').innerHTML = 'UnMute';
        }
      }
      else {
        //再生、停止
        if (player.getPlayerState() == 1) {
          player.pauseVideo();
          document.getElementById('message-overlay').style.display = 'none';
          document.getElementById('message').style.display = 'flex';
        }
        else {
          player.playVideo();
          document.getElementById('message-overlay').style.display = 'flex';
          document.getElementById('message').style.display = 'none';
        }
      }
    }
    else if (end_flag) {
      //location.href = "https://ohenro.online/ja/";
    }
  });

  // 1秒ごとに再生時間をチェックするタイマー
  setInterval(function() {
    if (player && player.getPlayerState() == YT.PlayerState.PLAYING) {
      let currentTime = player.getCurrentTime();
      if (currentTime >= 30) { 
        player.pauseVideo();
        player = null;
        document.getElementById('message-overlay').style.display = 'none';
        document.getElementById('message').innerHTML = '<a href="https://ohenro.online/ja/">続きを見たい方は、会員登録をしてください。</a>';
        document.getElementById('message').style.display = 'flex';
        end_flag = true;
      }
    }
  }, 1000);

