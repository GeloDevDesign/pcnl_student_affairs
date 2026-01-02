<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\EvaluationQuestion;

class EvaluationQuestionSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            'TEACHERS PERSONALITY' => [
                'My teacher is pleasant and refined in his/her actions and words and avolds irritating and disturbing mannerisms and movements.',
                'My teacher is respectable, well-dressed, well-groomed and behaves professionally.',
                'My teacher is physically and mentally alert and active',
                'My teacher shows willingness and enthusiasm to help me understand the lesson even after class hours',
                'I can apply what my teacher teaches to real life situations.',
                'My teacher shows enthusiasm to work/ to teach and finish tasks and shows pride and joy inhis/her profession.',
                'I feel accepted and respected as an individual by my teacher',
                'I see my teacher as a role model for positive behavior.',
                ' I feel free and confident to approach my teacher about academic matters.',
                'My teacher gives constructive comments and does not embarrass student.',
                'My teacher inspires me to examine other learning resources to help me gain a better and deeper understanding of the lesson.',
            ],
            'MASTERY OF THE SUBJECT' => [
                'My teacher shows mastery of the subject matter by providing clear explanations and enough examples to make the lesson easy to understand.',
                'My teacher introduces the lesson in an interesting manner and presents it in a well-organized way.',
                'My teacher mentions relevant, current and up-to-date information on the subject matter.',
                'My teacher uses grammatically correct language.',
                'My teacher can effectively communicate important concepts of the lesson.',
                'My teacher points at the relevance of the subject matter to my future profession.',
            ],
            'CLASSROOM MANAGEMENT' => [
                'My teacher meets our class regularly and uses an efficient method of performing class activities to avoid waste of time and effort.',
                'My teacher gives and discusses the syllabus/ course outline on the first week of classes.',
                "My teacher informs us of the coverage/ objective/ overview of the day's lesson and focuses on these in the development of the lesson.",
                'I get encouragement from my teacher to actively participate in teaching- learning activities, to think critically and analytically and to ask questlons',
                "My teachers gives enough and accurate evaluation of students' performancele (eg. class participation, quizzes, assignments, tests and other course requirements) and returns properly corrected papers within one or two weeks after the quiz/examinations or submission of the assignment",
                'My teacher uses adequate instructional materials and appropriate teaching strategies that make the lesson easy to understand',
                'My teacher enforces classroom policies uniformly to maintain a classroom situation appropriate to learning',
                'My teacher speaks in a modulated voice, loud and clear enough to be heard by the students.',
            ],
        ];

        foreach ($data as $category => $questions) {
            foreach ($questions as $questionText) {
                EvaluationQuestion::create([
                    'category' => $category,
                    'question_text' => $questionText,
                ]);
            }
        }
    }
}