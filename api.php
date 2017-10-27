<?php
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

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  Error('POST request method is expected.');
  return;
}

// get request body:
$requestBody = file_get_contents('php://input');

// check type of content:
if (strrpos($_SERVER['HTTP_CONTENT_TYPE'], '/json') !== FALSE || strrpos($_SERVER['CONTENT_TYPE'], '/json') !== FALSE) {
  // json:
  $query = json_decode($requestBody, true);
} else {
  // file:
  $query = array('method' => 'upload');
}

if (empty($query['method'])) {
  Error('The "method" is required. Value cannot be empty.');
  return;
}

require_once 'team.php';
require_once 'match.php';
require_once 'stage.php';

switch (strtolower($query['method'])) {
  case 'example':
    // return example data:
    OutputExampleData();
    break;

  case 'simulate':
    // return results of simulation:
    OutputSimulationResult($query['teams']);
    break;

  case 'upload':
    OutputData($requestBody);
    break;

  default:
    Error('Unknown method.');
    break;
}

/**
 * Gets data from data.csv.
 *
 * @return void
 */
function OutputExampleData() {
  if (($data = file_get_contents('data.csv')) !== FALSE) {
    OutputData($data);
  }
  else {
    Error('Cannot open file data.csv. Check the existence of "data.csv" and the permission to read it.');
  }
}

/**
 * Outputs the specified data.
 *
 * @param string $data
 *   CSV data to parse and output.
 *
 * @return void
 */
function OutputData($data) {
  if (empty($data)) {
    Error('Data is required.');
    return;
  }

  $skip = TRUE;
  $teams = array();

  $total = new Team();
  $total->Name = 'Total';
  $total->Games = $total->Goals = $total->Losses =
  $total->Missed = $total->Draw = $total->Wins = 0;

  $rows = split("\n", preg_replace('~\R~u', "\n", $data));

  foreach ($rows as $row) {
    if (empty($row)) {
      continue;
    }

    $row = str_getcsv($row, ';');

    if (count($row) < 6) {
      Error('Wrong data. Please check the file format.');
      return;
    }

    if ($skip) {
      $skip = FALSE;
      continue;
    }

    $team = new Team();

    $team->Name = trim($row[0]);
    $total->Games += ($team->Games = (int)$row[1]);
    $total->Wins += ($team->Wins = (int)$row[2]);
    $total->Losses += ($team->Losses = (int)$row[3]);
    $total->Draw += ($team->Draw = (int)$row[4]);

    $balls = split('-', $row[5]);

    $total->Goals += ($team->Goals = (int)trim($balls[0]));
    $total->Missed += ($team->Missed = (int)trim($balls[1]));

    // add team to list
    $teams[] = $team;
  }

  // add total results
  $teams[] = $total;

  Output($teams);
}

/**
 * Outputs simulations result.
 *
 * @param array $teams
 *   List of teams.
 *
 * @return void
 */
function OutputSimulationResult($teams) {
  $result = array();

  Play($result, $teams);
  Output($result);
}

/**
 * Simulates a stage of football matches.
 *
 * @param Stage[] &$writer
 *   List of stages for output.
 *
 * @param array $teams
 *   List of teams.
 *
 * @param int $stageNumber
 *   Stage number. Default: 1.
 *
 * @return void
 */
function Play(&$writer, $teams, $stageNumber = 1) {
  if (!isset($stageNumber)) {
    $stageNumber = 1;
  }

  // create new stage:
  $writer[] = $stage = new Stage();
  $stage->Matches = array();

  // teams count:
  $totalTeams = count($teams);

  // mix the array:
  shuffle($teams);

  // losers to remove:
  $losers = array();

  // simulate
  for ($i = 0; $i < $totalTeams; $i += 2) {
    $goalsAvg = array(
      $teams[$i]['Goals'] / $teams[$i]['Games'],
      $teams[$i + 1]['Goals'] / $teams[$i + 1]['Games']
    );
    $missedAvg = array(
      $teams[$i]['Missed'] / $teams[$i]['Games'],
      $teams[$i + 1]['Missed'] / $teams[$i + 1]['Games']
    );

    $stage->Matches[] = $match = new Match();

    $match->Teams = array($teams[$i]['Name'], $teams[$i + 1]['Name']);
    $match->Results = array(
      Poisson(
        ($goalsAvg[0] / $missedAvg[0]) * ($missedAvg[0] / $goalsAvg[0]) * $goalsAvg[0],
        ($goalsAvg[1] / $missedAvg[1]) * ($missedAvg[1] / $goalsAvg[1]) * $goalsAvg[1]
      ),
      Poisson(
        ($goalsAvg[1] / $missedAvg[1]) * ($missedAvg[1] / $goalsAvg[1]) * $goalsAvg[1],
        ($goalsAvg[0] / $missedAvg[0]) * ($missedAvg[0] / $goalsAvg[0]) * $goalsAvg[0]
      )
    );

    if ($match->Results[0] > $match->Results[1]) {
      $losers[] = $teams[$i + 1]['Name'];
    }
    else if ($match->Results[1] > $match->Results[0]) {
      $losers[] = $teams[$i]['Name'];
    }
    else {
      $losers[] = $teams[rand($i, $i + 1)]['Name'];
    }
  }

  // names of all teams to search index by name:
  $namesOfAllTeams = array_map(function($itm) { return $itm['Name']; }, $teams);

  // remove losers:
  foreach ($losers as $teamName) {
    if (($index = array_search($teamName, $namesOfAllTeams)) !== FALSE) {
      unset($teams[$index]);
    }
  }

  // next stage:
  if (count($teams) >= 2) {
    Play($writer, $teams, $stageNumber + 1);
  }
}

/**
 * Calculates factorial.
 *
 * @param double|integer $number
 *
 * @return double|integer
 */
function Factorial($number)
{
  if ($number < 2) {
    return 1;
  } else {
    return ($number * Factorial($number - 1));
  }
}

/**
 * Poisson Distribution.
 *
 * @param mixed $chance
 *   The probability value.
 *
 * @param mixed $occurrence
 *   The number of occurances.
 *
 * @return double
 */
function Poisson($chance, $occurrence)
{
  return round((exp(-$chance) * pow($chance, $occurrence) / Factorial($occurrence)) * 100, 2);
}

/**
 * Outputs error message.
 *
 * @param string $message
 *   Text of error message.
 *
 * @return void
 */
function Error($message) {
  Output(array('Error' => array('Text' => $message)), 500);
}

/**
 * Outputs response.
 *
 * @param mixed $data
 *   Data to output.
 *
 * @param int $status
 *   The HTTP status code. Default: 200 (OK).
 *
 * @return void
 */
function Output($data, $status = 200) {
  header('Content-Type: application/json');
  http_response_code($status);

  $result = json_encode($data);

  if ($result === FALSE)
  {
    throw new ErrorException('JSON encode error #' . json_last_error() . ': ' . json_last_error_msg());
  }

  echo $result;
}
