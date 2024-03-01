const getHiddenClubs = () => $('.lazy-clubs-list__item:hidden')
const clubItem = $('.lazy-clubs-list__item')

const clubsPerPage = 12

$(function () {
  clubItem.slice(0, clubsPerPage - 1).show()
})

$('#loadMore').on('click', function (e) {
  e.preventDefault()

  getHiddenClubs().slice(0, clubsPerPage - 1).slideDown()
  if (getHiddenClubs().length === 0) {
    $(this).hide()
    $('#loadLess').css('display', 'block')
  }
})

$('#loadLess').on('click', function (e) {
  e.preventDefault()
  clubItem.hide()
  clubItem.slice(0, clubsPerPage - 1).show()
  $(this).hide('slow')
  $('#loadMore').css('display', 'block')
})
