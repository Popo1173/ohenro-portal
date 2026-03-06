
  //言語選択時
  function select_language(lc) {
    $.post("user.php", {
      md: "select_language",
      lang_code: lc
    }, function(data){
    });
  }

  //お気に入りボタン押下時
  function click_favorite(flag, temple_num, movie_num, lang_code) {
    if (!flag) {
      add_favorite(temple_num, movie_num, lang_code);
      //ボタン変更処理

    }
    else {
      delete_favorite(temple_num, movie_num, lang_code);
      //ボタン変更処理

    }
  }

  //お気に入り追加時
  function add_favorite(temple_num, movie_num, lang_code) {
    $.post("user.php", {
      md: "movie_favorite",
      lang_code: lang_code,
      temple_num: temple_num,
      movie_num: movie_num
    }, function(data){
      alert("お気に入りに追加いたしました。");
    });
  }

  //お気に入り解除時
  function delete_favorite(temple_num, movie_num, lang_code) {
    $.post("user.php", {
      md: "delete_favorite",
      lang_code: lang_code,
      temple_num: temple_num,
      movie_num: movie_num
    }, function(data){
      alert("お気に入りを解除いたしました。");
    });
  }

