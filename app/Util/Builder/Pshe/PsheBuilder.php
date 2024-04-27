<?php

namespace App\Util\Builder\Pshe;

class PsheBuilder 
{
    public function index(bool $isTTWEnabled, bool $isUsteerEnabled, bool $isFootprintEnabled): array
    {
        return [
            "footprint" => [
                "title"  => "Footprints",
                "description" => "Teaching the STEER factors through a character-based curriculum!

                Equips pupils, teachers, and families to make the right choice at the right time, building self-awareness, social responsibility, and self-regulation.",
                "status"  => $isFootprintEnabled ? "active" : "inactive",
                "url" => $isFootprintEnabled ? "" : "https://youtu.be/mKgEdj9yWHw",
                "image" => "footprint",
                "age" => "8-13"
            ],
            "take_the_wheel" => [
                "title"  => "Take The Wheel",
                "description" => "
                Guiding each student through their own STEER journey!
                
                Equips students to understand how they have been steering and how they want to steer in the future; building metacognition, self-efficacy, and self-regulation
                ",
                "status"  => $isTTWEnabled ? "active" : "inactive",
                "url" => $isTTWEnabled ? "" : "https://www.youtube.com/watch?v=FrbV_F-nfxQ",
                "image" => "take-the-wheel",
                "age" => "15"
            ],
            "usteer" => [
                "title"  => "USteer",
                "description" => "Training and accrediting students with critical workplace soft skills

                Equips 16-18 year olds with the soft skills needed to succeed beyond school - in an apprenticeship, a university course, the workplace and life
                ",
                "status"  => $isUsteerEnabled ? "active" : "inactive",
                "url" => $isUsteerEnabled ? "" : "https://www.usteer.io/usteer-course-introduction",
                "image" => "usteer",
                "age" => "16-18"
            ],
        ];
    }
}