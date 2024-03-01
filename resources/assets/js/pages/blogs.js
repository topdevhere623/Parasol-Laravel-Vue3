// sort
$('.blog-container .custom-select input').click(function (e) {
  if ($(e.target).attr('checked') !== 'checked') {
    window.location = $(e.target).data('link')
  }
})

// search
$('.blog-container .search-box input').keypress(function (e) {
  if (e.which === 13) {
    searchBlogs($(e.target));
  }
})

$('.blog-container .search-box img').click(function (e) {
  searchBlogs($(e.target).prev())
})

const searchBlogs = (input) => {
  let link = input.data('link');
  const query = input.val();
  if (query) {
    if (link.indexOf('?') === -1) {
      link += '?';
    } else {
      link += '&';
    }
    link += 'query=' + query;
  }

  window.location = link;
}