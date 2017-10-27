/*
 * Copyright (c) 2017 @meet-aleksey <https://github.com/meet-aleksey>
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

/** Constant: API Url. */
var API_URL = 'api.php';

/** Current list of teams. */
var teams = [];

/**
 * Sends a request to the API.
 *
 * @param {object} data Data to send.
 * @param {function} success Success response callback.
 */
function apiRequest(data, success) {
  $.ajax({
    type: 'POST',
    url: API_URL,
    data: JSON.stringify(data),
    dataType: 'json',
    contentType: 'application/json',
    success: success,
    error: showError
  });
}

/**
 * Displays a error message.
 *
 * @param {jqXHR} xhr jqXHR instanse.
 * @param {stirng} textStatus Status.
 * @param {string} errorThrown Error message.
 */
function showError(xhr, textStatus, errorThrown) {
  var errorMessage = (xhr.responseJSON ? xhr.responseJSON.Error.Text : xhr.statusText);

  console.error(errorMessage);

  var messages = $('#messages');

  $('<hr />').appendTo(messages);
  $('<div class="error" />').text(errorMessage).appendTo(messages);
}

/**
 * Displays a table of teams.
 */
function showTeams() {
  var container = $('#teams').empty();
  var table = $('<table />');
  var thead = $('<thead />');
  var tbody = $('<tbody />');
  var row = $('<tr />');
  var cell = null;
  var details = null;

  row.appendTo(thead);
  thead.appendTo(table);
  tbody.appendTo(table);

  $('<hr />').appendTo(container);
  $('<h1>Teams</h1>').appendTo(container);

  table.appendTo(container);

  $('<th>Country</th>').appendTo(row);
  $('<th>Total games</th>').appendTo(row);
  $('<th>Wins</th>').appendTo(row);
  $('<th>Draws</th>').appendTo(row);
  $('<th>Losses</th>').appendTo(row);
  $('<th>Scored goals</th>').appendTo(row);
  $('<th>Missed goals</th>').appendTo(row);
  $('<th>Average goals</th>').appendTo(row);
  $('<th>Average missed</th>').appendTo(row);
  $('<th>Attack</th>').appendTo(row);
  $('<th>Defense</th>').appendTo(row);

  for (var i = 0; i < teams.length; i++) {
    var team = teams[i];
    var goalsAvg = (team.Goals / team.Games).toFixed(5);
    var missedAvg = (team.Missed / team.Games).toFixed(5);

    row = $('<tr />');

    if (i === (teams.length - 1)) {
      row.addClass('total');
    }

    $('<td />').text(team.Name).appendTo(row);
    $('<td />').text(team.Games).addClass('games').appendTo(row);
    $('<td />').text(team.Wins).addClass('wins').appendTo(row);
    $('<td />').text(team.Draw).addClass('draw').appendTo(row);
    $('<td />').text(team.Losses).addClass('losses').appendTo(row);
    $('<td />').text(team.Goals).addClass('goals').appendTo(row);
    $('<td />').text(team.Missed).addClass('missed').appendTo(row);

    cell = $('<td />').text(goalsAvg).addClass('goals-avg').appendTo(row);
    details = $('<div />').addClass('details').appendTo(cell);
    $('<span />').text(team.Goals).addClass('goals').appendTo(details);
    $('<span />').text(' / ').appendTo(details);
    $('<span />').text(team.Games).addClass('games').appendTo(details);

    cell = $('<td />').text(missedAvg).addClass('missed-avg').appendTo(row);
    details = $('<div />').addClass('details').appendTo(cell);
    $('<span />').text(team.Missed).addClass('missed').appendTo(details);
    $('<span />').text(' / ').appendTo(details);
    $('<span />').text(team.Games).addClass('games').appendTo(details);

    cell = $('<td />').text((goalsAvg / missedAvg).toFixed(5)).addClass('attack').appendTo(row);
    details = $('<div />').addClass('details').appendTo(cell);
    $('<span />').text(goalsAvg).addClass('goals-avg').appendTo(details);
    $('<span />').text(' / ').appendTo(details);
    $('<span />').text(missedAvg).addClass('missed-avg').appendTo(details);

    cell = $('<td />').text((missedAvg / goalsAvg).toFixed(5)).addClass('defense').appendTo(row);
    details = $('<div />').addClass('details').appendTo(cell);
    $('<span />').text(missedAvg).addClass('missed-avg').appendTo(details);
    $('<span />').text(' / ').appendTo(details);
    $('<span />').text(goalsAvg).addClass('goals-avg').appendTo(details);

    row.appendTo(tbody);
  }

  $('span', '.details').mouseenter(function () {
    if ($(this).attr('class')) {
      var row = $(this).parents('tr');
      var className = $(this).attr('class');

      $('.' + className, row).addClass('highlight');
    }
  }).mouseleave(function () {
    if ($(this).attr('class')) {
      var row = $(this).parents('tr');
      var className = $(this).attr('class').split(' ').filter(function (item) {
        return item !== 'highlight';
      });

      $('.' + className, row).removeClass('highlight');
    }
  });

  $('#messages').empty();
}

/**
 * Displays results of football simulation.
 *
 * @param {array} stages List of stages.
 */
function showSimulationResults(stages) {
  var container = $('#simulation').empty();

  for (var i = 0; i < stages.length; i++) {
    var stage = stages[i];
    var table = $('<table />').addClass('stage');
    var thead = $('<thead />');
    var tbody = $('<tbody />');
    var row = $('<tr />');
    var cell = null;
    var details = null;

    row.appendTo(thead);
    thead.appendTo(table);
    tbody.appendTo(table);

    $('<hr />').appendTo(container);
    $('<h2 />').text('Stage ' + (i + 1)).appendTo(container);

    table.appendTo(container);

    $('<th>Match</th>').appendTo(row);
    $('<th>Winner</th>').appendTo(row);
    $('<th>Loser</th>').appendTo(row);

    for (var j = 0; j < stage.Matches.length; j++) {
      var match = stage.Matches[j];

      row = $('<tr />');

      $('<td />').text(match.Teams.join(' - ')).appendTo(row);

      cell = $('<td />').text(match.Results[0] > match.Results[1] ? match.Teams[0] : match.Teams[1]).addClass('winner').appendTo(row);
      details = $('<div />').addClass('details').appendTo(cell);
      $('<span />').addClass('percent').text(Math.max(match.Results[0], match.Results[1]) + '%').appendTo(details);
      
      cell = $('<td />').text(match.Results[0] > match.Results[1] ? match.Teams[1] : match.Teams[0]).addClass('loser').appendTo(row);
      details = $('<div />').addClass('details').appendTo(cell);
      $('<span />').addClass('percent').text(Math.min(match.Results[0], match.Results[1]) + '%').appendTo(details);

      row.appendTo(tbody);
    }
  }

  $('#messages').empty();

  $('html, body').animate({
    scrollTop: $("#simulation").offset().top
  }, 500);
}

function btnUseExample_Click() {
  apiRequest({ method: 'example' }, function (response) {
    teams = response;
    showTeams();

    $('#btnStart').show('slow');
  });
}

function btnUploadOwnFile_Click() {
  $('#uploadFile').click();
}

function uploadFile_Change() {
  if (this.files.length <= 0) {
    return;
  }

  $.ajax({
    type: 'POST',
    url: API_URL,
    cache: false,
    contentType: false,
    processData: false,
    data: this.files[0],
    success: function (response) {
      teams = response;
      showTeams();

      $('#btnStart').show('slow');
    },
    error: showError
  });
}

function btnStart_Click() {
  apiRequest({ method: 'simulate', teams: teams.slice(0, teams.length - 1) }, function (response) {
    showSimulationResults(response);
  });
}

$(document).ready(function () {
  $('#btnStart').hide();

  $('#btnUseExample').click(btnUseExample_Click);
  $('#btnUploadOwnFile').click(btnUploadOwnFile_Click);
  $('#btnStart').click(btnStart_Click);
  $('#uploadFile').change(uploadFile_Change);
});