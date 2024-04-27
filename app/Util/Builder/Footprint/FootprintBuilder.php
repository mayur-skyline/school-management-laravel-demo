<?php
namespace App\Util\Builder\Footprint;
use App\Services\AstNextFootprintServiceProvider;

class FootprintBuilder
{
    function prepareResourecesstage1($request)
    {
        $AstNextFootprintServiceProvider = new AstNextFootprintServiceProvider();
        $prepare_resource1 = $AstNextFootprintServiceProvider->footprintResources('', 'stage_1', 8, $request,false,true);
        $prepare_resource2 = $AstNextFootprintServiceProvider->footprintResources('', 'stage_1', 1, $request,false,true);
        $resorces = array_merge($prepare_resource1, $prepare_resource2);
        $blue_resource1 = $AstNextFootprintServiceProvider->footprintResources('', 'stage_1', 3, $request,false,false);
        $blue_resource2 = $AstNextFootprintServiceProvider->footprintResources('', 'stage_1', 2, $request,false,true);
        $blueresources = array_merge_recursive($blue_resource1, $blue_resource2);

        $footprint['lessons'] = [
            [
                'title' => 'Before you Start teaching',
                'description' => 'Click here to watch the training ppt.,download the Schema of Work and display materials!',
                'resources' => (object) $resorces
            ],
            [
                'title' => 'Social Footprint',
                'description' => 'My footprint is the impact I have on others. My footprint can be thoughtful or thoughtless; my footprint is my responsibility.',
                'resources' => (object)$AstNextFootprintServiceProvider->footprintResources('', 'stage_1', 2, $request,false,false)
            ],
            [
                'title' => 'Blue Footprints',
                'description' => 'Blue Footprints are Strong and Firm. I can leave Blue Footprints when I want others to feel safe, reassured, and certain',
                'resources' => (object) $blueresources
            ],
            [
                'title' => 'Yellow Footprints',
                'description' => 'Yellow Footprints are Careful and Patient. I can leave Yellow Footprints when I want others to feel noticed, valued, and trusted',
                'resources' => (object)$AstNextFootprintServiceProvider->footprintResources('', 'stage_1', 4, $request)
            ],
            [
                'title' => 'Green Footprints',
                'description' => 'Green Footprints are Bold and Dynamic. I can leave Green Footprints when I want others to feel motivated, energised, and challenged',
                'resources' => (object)$AstNextFootprintServiceProvider->footprintResources('', 'stage_1', 5, $request)
            ],
            [
                'title' => 'Orange Footprints',
                'description' => 'Orange Footprints are Busy and Helpful. I can leave Orange Footprints when I want others to feel supported, encouraged, and empowered',
                'resources' => (object)$AstNextFootprintServiceProvider->footprintResources('', 'stage_1', 6, $request)
            ],
            [
                'title' => 'My Footprints',
                'description' => 'Each footprint has a different impact. I can choose to leave the right footprint at the right time!',
                'resources' => (object)$AstNextFootprintServiceProvider->footprintResources('', 'stage_1', 7, $request)
            ],
        ];
        return $footprint;
    }

    function prepareResourecesstage2($request)
    {
        $AstNextFootprintServiceProvider = new AstNextFootprintServiceProvider();
        $prepare_res1 = $AstNextFootprintServiceProvider->footprintResources('', 'stage_2', 1, $request,false,true);
        $prepare_res2 =
        $AstNextFootprintServiceProvider->footprintResources('', 'stage_2', 8, $request);
        $final_res = array_merge($prepare_res1, $prepare_res2);

        $footprint['lessons'] = [
            [
                'title' => 'Before you Start teaching',
                'description' => 'Click here to watch the training ppt.,download the Schema of Work and display materials!',
                'resources' => (object) $final_res,
            ],
            [
                'title' => 'Discovering our Space',
                'description' => 'We all have our own unique Space in the world; it’s been developing since the moment we were born! We explore four different parts of our Space',
                'resources' => (object)$AstNextFootprintServiceProvider->footprintResources('', 'stage_2', 2, $request)
            ],
            [
                'title' => 'Exploring our Pleasing Space',
                'description' => 'We step into our Pleasing Space when we doubt ourselves and trust others. We need to choose the right time to step into our Pleasing Space!',
                'resources' => (object)$AstNextFootprintServiceProvider->footprintResources('', 'stage_2', 3, $request)
            ],
            [
                'title' => 'Exploring our Succeeding Space',
                'description' => 'We step into our Succeeding Space when we trust ourselves and doubt others. We need to choose the right time to step into our Succeeding Space!',
                'resources' => (object)$AstNextFootprintServiceProvider->footprintResources('', 'stage_2', 4, $request)
            ],
            [
                'title' => 'Exploring our Protecting Space',
                'description' => 'We step into our Protecting Space when we doubt ourselves and doubt others too. We need to choose the right time to step into our Protecting Space!',
                'resources' => (object)$AstNextFootprintServiceProvider->footprintResources('', 'stage_2', 5, $request)
            ],
            [
                'title' => 'Exploring our Relaxing Space',
                'description' => 'We step into our Relaxing Space when we trust ourselves and trust others too. We need to choose the right time to step into our Relaxing Space!',
                'resources' => (object)$AstNextFootprintServiceProvider->footprintResources('', 'stage_2', 6, $request)
            ],
            [
                'title' => "Our Explorer's Journal",
                'description' => 'We think about all we discovered in our exploration! We draw our own map, so we can step into the right Space at the right time!',
                'resources' => (object)$AstNextFootprintServiceProvider->footprintResources('', 'stage_2', 7, $request)
            ],
        ];
        return $footprint;
    }

    function prepareResourecesstage1support($request)
    {
        $AstNextFootprintServiceProvider = new AstNextFootprintServiceProvider();
        $supporting_resorces1 = $AstNextFootprintServiceProvider->footprintResources('supporting_resources', 'stage_1', 8, $request,true);
        $tmp_support = $supporting_resorces1;
        $tmp_support1 = $supporting_resorces1;
        unset($tmp_support[1]);
        unset($tmp_support1[0]);
        $tmp_support1 = array_values(array_filter($tmp_support1));
        $supporting_resorces2 = $AstNextFootprintServiceProvider->footprintResources('supporting_resources', 'stage_1', 9, $request, true);
        $supporting_resorces3 = $AstNextFootprintServiceProvider->footprintResources('supporting_resources', 'stage_1', 10, $request, true);
        $supporting_resorces4 = $AstNextFootprintServiceProvider->footprintResources('supporting_resources', 'stage_1', 11, $request, true);
        $supporting_resorces5 = $AstNextFootprintServiceProvider->footprintResources('supporting_resources', 'stage_1', 1, $request);
        $supportingresources =  [
            'supporting_resources' => (object)[
                'teaching_training' => [
                    'title' => "Teacher Training",
                    'resources' => $tmp_support,
                ],
                'scheme_of_work' => [
                    'title' => "Scheme of Work",
                    'resources' => $tmp_support1,
                ],
                'preparing_your_footprints_display_board' => [
                    'title' => "Preparing your Footprints Display Board",
                    'resources' => $supporting_resorces5,
                ],
                'footprints_for_teachers' => [
                    'title' => "Footprints for Teachers",
                    'resources' => $supporting_resorces2,
                ],
                'display_board_materials' => [
                    'title' => "Display Board Materials",
                    'resources' => $supporting_resorces3,
                ],
                'family_footprints' => [
                    'title' => "Family Footprints",
                    'resources' => $supporting_resorces4,
                ]
            ],
        ];
        return $supportingresources;
    }

    function prepareResourecesstage2support($request)
    {

        $AstNextFootprintServiceProvider = new AstNextFootprintServiceProvider();
        $supporting_resorces1 = $AstNextFootprintServiceProvider->footprintResources('supporting_resources', 'stage_2', 1, $request);
        $supporting_resorces2 = $AstNextFootprintServiceProvider->footprintResources('supporting_resources', 'stage_2', 8, $request, true);
        $supporting_resorces3 = $AstNextFootprintServiceProvider->footprintResources('supporting_resources', 'stage_2', 9, $request, true);
        $supporting_resorces4 = $AstNextFootprintServiceProvider->footprintResources('supporting_resources', 'stage_2', 10, $request, true);
        $supporting_resorces5 = $AstNextFootprintServiceProvider->footprintResources('supporting_resources', 'stage_2', 7, $request,true);
        $supporting_resorces6 = $AstNextFootprintServiceProvider->footprintResources('supporting_resources', 'stage_2', 7, $request, false);
        $total_resources = array_merge($supporting_resorces4, $supporting_resorces6);
        $supportingresources =  [
            'supporting_resources' => (object)[
                'teaching_training' => [
                    'title' => "Teacher Training",
                    'resources' => $supporting_resorces2
                ],
                'scheme_of_work' => [
                    'title' => "Scheme of Work",
                    'resources' => $supporting_resorces2
                ],
                'preparing_your_footprints_display_board' => [
                    'title' => "preparing your footprints display board",
                    'resources' => $supporting_resorces1,
                ],
                'footprints_for_teachers' => [
                    'title' => "footprints for teachers",
                    'resources' => $supporting_resorces5,
                ],
                'display_board_materials' => [
                    'title' => "display board materials",
                    'resources' => $supporting_resorces3,
                ],
                'family_footprints' => [
                    'title' => "family footprints",
                    'resources' => $total_resources,
                ]
            ],
        ];
        return $supportingresources;
    }

    function myfootprint()
    {
        $AstNextFootprintServiceProvider = new AstNextFootprintServiceProvider();
        $footprint['footprints'] = [
            [
                'id' => 1,
                'title' => 'My Footprint',
                'description' =>  'My footprint is the impact I leave on others. I discover 4 different footprints I can leave on others; and learn how I can choose to leave the right footprint at the right time!',
            ],
            [
                'id' => 2,
                'title' => 'My Space',
                'description' =>  'My Space is what makes me ME! I discover 4 different parts of my Space, and learn that I can choose to step into different parts of my Space at different times!',
            ],
            [
                'id' => 3,
                'title' => 'My Stage',
                'description' =>  'My Stage is my story! I discover my front stage, my backstage, and my trust curtain! I learn that I can choose what I share with others, and what I keep private!',
            ]
        ];
        $footprint['videos'] = [
            [
                'id' => 1,
                'url' => 'https://www.youtube.com/embed/mKgEdj9yWHw',
                'title' => $AstNextFootprintServiceProvider->customurltodesc('https://www.youtube.com/watch?v=mKgEdj9yWHw&t'),
                'duration' => $AstNextFootprintServiceProvider->customurltoduration('https://www.youtube.com/watch?v=mKgEdj9yWHw&t')
            ],
            [
                'id' => 2,
                'url' => 'https://www.youtube.com/embed/b3YTHoCOa7g',
                'title' => $AstNextFootprintServiceProvider->customurltodesc('https://www.youtube.com/watch?v=b3YTHoCOa7g'),
                'duration' => $AstNextFootprintServiceProvider->customurltoduration('https://www.youtube.com/watch?v=b3YTHoCOa7g')
            ],
            [
                'id' => 3,
                'url' => 'https://www.youtube.com/embed/_atO1gmlRas',
                'title' => $AstNextFootprintServiceProvider->customurltodesc('https://www.youtube.com/watch?v=_atO1gmlRas'),
                'duration' => $AstNextFootprintServiceProvider->customurltoduration('https://www.youtube.com/watch?v=_atO1gmlRas')
            ]
        ];
        return $footprint;
    }
}
