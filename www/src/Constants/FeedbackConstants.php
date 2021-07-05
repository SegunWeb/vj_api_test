<?php

namespace App\Constants;

class FeedbackConstants
{
	const IMPROVEMENT = 0, PROBLEMS = 1, QUESTION = 2;
	
	const IMPROVEMENT_TITLE = 'Предложение улучшения', PROBLEMS_TITLE = 'Сообщение о проблеме', QUESTION_TITLE = 'Новый вопрос';
	
	static public function loadFeedbackValues()
	{
		return [
			self::IMPROVEMENT_TITLE => self::IMPROVEMENT,
			self::PROBLEMS_TITLE    => self::PROBLEMS,
			self::QUESTION_TITLE    => self::QUESTION
		];
	}
}