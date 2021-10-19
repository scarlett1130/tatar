<?php

class AdmincpQuestionsModel extends ModelBase
{
     public function getQuestions()
     {
          return $this->provider->fetchResultSet( "SELECT * FROM questions");
     }
     public function DeleteQuestion($id)
     {
          return $this->provider->executeQuery( "DELETE FROM questions WHERE id=%s", array($id));
     }
     public function AddQuestion($question, $correct_answer, $answer_1, $answer_2, $answer_3, $answer_4)
     {
          $this->provider->executeQuery2("INSERT INTO questions SET question='%s', correct_answer='%s', answer_1='%s', answer_2='%s', answer_3='%s', answer_4='%s', date_add='%s'", array($question, $correct_answer, $answer_1, $answer_2, $answer_3, $answer_4, time()));
     }
     public function RestartQuestions()
     {
          $this->provider->executeQuery2("UPDATE p_players SET num_questions=0");
     }
}
?>