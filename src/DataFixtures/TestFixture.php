<?php

namespace App\DataFixtures;

use App\Entity\Course;
use App\Entity\Lesson;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;

class TestFixture extends Fixture implements FixtureGroupInterface
{
    public function load(ObjectManager $manager): void
    {
        $coursesData = [
            [
                'code' => 'web_dev',
                'name' => 'Веб-разработка',
                'description' => 'Курс, посвящённый созданию современных веб-приложений с использованием HTML, CSS, JavaScript и серверных технологий.',
                'lessons' => [
                    [
                        'name' => 'Основы HTML и CSS',
                        'content' => 'Изучение базовой разметки веб-страниц, каскадных таблиц стилей и принципов адаптивного дизайна.',
                        'sort' => 1,
                    ],
                    [
                        'name' => 'Введение в JavaScript',
                        'content' => 'Основы языка JavaScript, работа с DOM и создание интерактивных элементов на странице.',
                        'sort' => 2,
                    ],
                    [
                        'name' => 'Работа с формами и AJAX',
                        'content' => 'Обработка данных форм, валидация и динамическая загрузка данных с использованием AJAX-запросов.',
                        'sort' => 3,
                    ],
                ],
            ],
            [
                'code' => 'prog_basics',
                'name' => 'Основы программирования',
                'description' => 'Курс для знакомства с базовыми концепциями программирования на PHP, включающий основные принципы алгоритмизации и объектно-ориентированного подхода.',
                'lessons' => [
                    [
                        'name' => 'Введение в программирование',
                        'content' => 'Обзор истории программирования, основных понятий и ролей разработчика в современном мире.',
                        'sort' => 1,
                    ],
                    [
                        'name' => 'Переменные и типы данных',
                        'content' => 'Изучение переменных, скалярных и составных типов данных, примеры использования в PHP.',
                        'sort' => 2,
                    ],
                    [
                        'name' => 'Условные конструкции и циклы',
                        'content' => 'Рассмотрение операторов ветвления и циклических структур, примеры практического применения.',
                        'sort' => 3,
                    ],
                ],
            ],
        ];

        // Создаём курсы и привязываем к ним уроки
        foreach ($coursesData as $courseData) {
            $course = new Course();
            $course->setCode($courseData['code']);
            $course->setName($courseData['name']);
            $course->setDescription($courseData['description']);

            foreach ($courseData['lessons'] as $lessonData) {
                $lesson = new Lesson();
                // Связываем урок с курсом
                $lesson->setCourse($course);
                $lesson->setName($lessonData['name']);
                $lesson->setContent($lessonData['content']);
                $lesson->setOrderNumber($lessonData['sort']);

                // Добавляем урок в коллекцию курса (если метод addLesson реализован)
                $course->addLesson($lesson);

                // Сохраняем урок
                $manager->persist($lesson);
            }

            // Сохраняем курс
            $manager->persist($course);
        }

        // Фиксируем изменения в базе данных
        $manager->flush();
    }

    public static function getGroups(): array
    {
        return ['test'];
    }
}
