<?php
namespace App\Util\Builder\AstNextResources;
use App\Services\AstNextPupilAssessmentProvider;

class ResourcesBuilder
{
    function astnextresources()
    {
        $final_response['send'] = [
            [
                'title' => 'FAQs for SENDCos',
                'url' => fetchastresources("training/SEND/STEER FAQs for SENDCos and Learning Support Teachers 05082022.pdf")
            ],
            [
                'title' => 'STEER Tracking and ADHD',
                'url' => fetchastresources("training/SEND/STEER Tracking and ADHD 05082022.docx.pdf")
            ],
            [
                'title' => 'STEER Tracking and ASD',
                'url' => fetchastresources("training/SEND/STEER Tracking and ASD 05082022.docx.pdf")
            ],
            [
                'title' => 'STEER Tracking and SEND',
                'url' => fetchastresources("training/SEND/STEER Tracking and SEND 05082022.docx.pdf")
            ],
            [
                'title' => 'A podcast with a school who use STEER to support students with ASD',
                'url' => "https://www.youtube.com/embed/ZBRzcNSCpb0"
            ],
        ];
        $final_response['mental_health_and_wellbeing'] = [
            [
                'title' => 'STEER and Eating Disorders',
                'url' => fetchastresources("training/Welfare and Mental Health/STEER and Eating Disorders 05092022.pdf")
            ],
            [
                'title' => 'STEER and Self Harm',
                'url' => fetchastresources("training/Welfare and Mental Health/STEER and Self-Harm 05082022.pdf")
            ],
            [
                'title' => 'STEER and Anxiety Disorders',
                'url' => fetchastresources("training/Welfare and Mental Health/STEER and Anxiety Disorders 05082022.pdf")
            ],
            [
                'title' => 'STEER FAQs for School Counsellors and Psychologists',
                'url' => fetchastresources("training/Welfare and Mental Health/STEER FAQs for School Counsellors and Psychologists 05082022.pdf")
            ],
        ];
        $final_response['introducing_steer_to_your_school'] = [
            [
                'title' => "Can St Bart's High Help Ellie?",
                'url' => 'https://www.youtube.com/embed/xozBomsHCA8'
            ],
            [
                'title' => "Adolescence! We'll Help you Steer it",
                'url' => 'https://www.youtube.com/embed/oDxpRlsLWkg'
            ],
            [
                'title' => 'Introduction to STEER Tracking / Safeguarding Demonstration Video ',
                'url' => 'https://www.youtube.com/embed/RVd0-U3pack'
            ],
            [
                'title' => 'STEER Tracking Template Letter to Parents and Carers',
                'url' => fetchastresources("training/Introducing STEER to your school/STEER Tracking Template lLetter to Parents and Carers .docx")
            ],
            [
                'title' => 'Listen to a podcast interview with one of our team',
                'url' => "https://cornerstoneseducation.co.uk/podcasts/tackling-the-mental-health-and-well-being-of-young-people/"
            ],
            [
                'title' => 'How to Introduce STEER Tracking to Parents, Colleagues and Students',
                'url' => fetchastresources("training/Introducing STEER to your school/How to Introduce STEER Tracking to Students, parents and colleagues.pdf")
            ],
            [
                'title' => 'Seven Ways to Use STEER Tracking Data Retrospectively and Proactively',
                'url' => fetchastresources("training/Introducing STEER to your school/STEER Seven Ways to Use STEER Tracking Data Retrospectively and Proactively 05082022.docx.pdf")
            ],
            [
                'title' => 'FAQs from Parents or Carers Enquiring about STEER Tracking',
                'url' => fetchastresources("training/Introducing STEER to your school/STEER FAQs from Parents or Carers Enquiring about STEER Tracking.pdf")
            ],
            [
                'title' => 'STEER website',
                'url' => 'https://steer.education/'
            ],
            [
                'title' => 'How is the STEER assessment different from traditional surveys',
                'url' => fetchastresources("training/Introducing STEER to your school/How is the STEER assessment different from traditional surveys.pdf")
            ],
        ];
        $final_response['leading_assessments'] = [
            [
                'title' => 'STEER Tracking Assessment Planning Checklist',
                'url' => fetchastresources("training/Facilitating Assessments/STEER Tracking Assessment Planning Checklist 30.08.2022.pdf")
            ],
            [
                'title' => 'How to Ensure STEER Tracking is Accessible to Younger Students',
                'url' => fetchastresources("training/Facilitating Assessments/How to Ensure STEER Tracking is Accessible to Younger Students 04.08.22.pdf")
            ],
            [
                'title' => 'How to Ensure the STEER Tracking Assessment is Accessible to EAL students',
                'url' => fetchastresources("training/Facilitating Assessments/How to Ensure the STEER Tracking Assessment is Accessible to EAL students 04.08.2022.pdf")
            ],
            [
                'title' => 'How to Ensure the STEER Tracking Assessment is Accessible to Students with ASD',
                'url' => fetchastresources("training/Facilitating Assessments/How to Ensure the STEER Tracking Assessment is Accessible to Students with ASD 04.08.2022.pdf")
            ],
            [
                'title' => 'How to Ensure the STEER Tracking Assessment is Accessible to Students with SpLD',
                'url' => fetchastresources("training/Facilitating Assessments/How to Ensure the STEER Tracking Assessment is Accessible to Students with SpLD 04.08.2022.pdf")
            ],
            [
                'title' => 'How to Ensure the STEER Tracking Assessment is Culturally Accessible',
                'url' => fetchastresources("training/Facilitating Assessments/How to Ensure the STEER Tracking Assessment is Culturally Accessible 04.08.2022.pdf")
            ],
            [
                'title' => 'Can STEER Tracking be Translated and Used across Cultures',
                'url' => fetchastresources("training/Facilitating Assessments/Can STEER Tracking be translated and used across cultures 04.08.2022.pdf")
            ],
            [
                'title' => 'Why Students  in Y3-Y4 can Complete the STEER Tracking Assessment',
                'url' => fetchastresources("training/Facilitating Assessments/Why Students  in Y3-Y4 can Complete the STEER Tracking Assessment 04.08.2022.pdf")
            ],
        ];
        $final_response['inspection'] = [
            [
                'title' => 'A podcast explaining how STEER supports inspection',
                'url' => "https://drive.google.com/file/d/1Fl5DvRuM9rWG09WB7SjKcxqPV-3lSjvi/view?usp=sharing",
            ],
            [
                'title' => 'STEER and the New ISI Inspection Framework',
                'url' => fetchastresources("training/inspection/Steer Tracking and the new ISI Inspection Framework 05082022.pdf")
            ],
            [
                'title' => 'STEER and New OFSTED Inspection Framework',
                'url' => fetchastresources("training/inspection/How STEER Tracking can Support the OFSTED EIF 2019.pdf")
            ],
        ];
        return $final_response;
    }

    function astnextadminresources(){
        $final_response['technical'] = [
//            [
//                'title' => 'STEER Information to Support the Technical Set Up for STEER Tracking',
//                'url' => fetchastresources("admin/Technical/STEER Information to Support the Technical set up for STEER.pdf")
//            ],
//            [
//                'title' => 'STEER How to Import CSV Files to your School Platform ',
//                'url' => fetchastresources("admin/Technical/STEER How to Import your CSV Files to your School Platform.pdf")
//            ],
//            [
//                'title' => 'STEER Adding Level 4 Practitioners to your School Platform',
//                'url' => fetchastresources("admin/Technical/STEER Adding Level 4 Practitioners to your School Platform.pdf")
//            ],
//            [
//                'title' => 'STEER Template CSV for Name Code Generation',
//                'url' => fetchastresources("admin/Technical/STEER TEMPLATE CSV for NAME CODE GENERATION.xlsx")
//            ],
//            [
//                'title' => 'STEER Wonde API Integration -10 Key Points',
//                'url' => fetchastresources("admin/Technical/STEER Wonde API Integration_10 Key Points.pdf")
//            ],
//            [
//                'title' => 'STEER Setting up and using the Wonde API',
//                'url' => fetchastresources("admin/Technical/STEER Setting up and using the Wonde API.pdf")
//            ],
//            [
//                'title' => 'STEER Setting Permissions in the Wonde Dash',
//                'url' => fetchastresources("admin/Technical/STEER Setting Permissions in the Wonde Dash.pdf")
//            ],
//            [
//                'title' => 'Wonde Access Control Settings',
//                'url' => fetchastresources("admin/Technical/Wonde Access Control Settings.pdf")
//            ],
//            [
//                'title' => 'STEER Wonde Scoping document',
//                'url' => fetchastresources("admin/Technical/STEER Wonde Scoping Document.docx")
//            ],
            [
                'title' => 'How to Sync your Data with Wonde',
                'url' => fetchastresources("admin/Technical/How to sync your data with Wonde.pdf")
            ],
            [
                'title' => 'What optional data should we upload to STEER',
                'url' => fetchastresources("admin/Technical/What optional data should we upload to STEER.docx")
            ],
            [
                'title' => 'STEER Allow-listing URLs',
                'url' => fetchastresources("admin/Technical/STEER Allow-listing URLs.pdf")
            ],
        ];
        $final_response['data_protection'] = [
//            [
//                'title' => 'STEER User_Data Processing and Terms of Use 2022',
//                'url' => fetchastresources("admin/Data Protection/STEER User_Data Processing and Terms of Use 2022.pdf"),
//            ],
            [
                'title' => 'STEER Template SSM Code of Conduct',
                'url' => fetchastresources("admin/Data Protection/STEER Template SSM Code of Conduct.pdf"),
            ],
            [
                'title' => 'STEER Data Collection Retention and Security',
                'url' => fetchastresources("admin/Data Protection/STEER Data Collection Retention and Security.pdf")
            ],
//            [
//                'title' => 'STEER Data Protection Policy [GDPR]',
//                'url' => fetchastresources("admin/Data Protection/STEER Education Ltd Data Protection Policy [UK GDPR].pdf")
//            ],
            [
                'title' => 'STEER Education Ltd Data Protection Policy [UK GDPR] Client',
                'url' => fetchastresources("admin/Data Protection/STEER Education Ltd Data Protection Policy [UK GDPR] Client.pdf")
            ],
            [
                'title' => 'STEER Privacy Policy Information TEMPLATE for schools',
                'url' => fetchastresources("admin/Data Protection/STEER Privacy Policy Information TEMPLATE for schools.docx")
            ],
            [
                'title' => 'STEER Privacy Policy',
                'url' => fetchastresources("admin/Data Protection/STEER Privacy Policy.pdf")
            ],
            [
                'title' => 'STEER Privacy Policy for School Staff',
                'url' => fetchastresources("admin/Data Protection/STEER Privacy Policy for School Staff.pdf")
            ],
            [
                'title' => 'STEER Privacy Policy for Students Mobile App',
                'url' => fetchastresources("admin/Data Protection/STEER Privacy Policy for Students_Mobile App.pdf")
            ],
            [
                'title' => 'STEER Privacy Policy for StudentsTake The Wheel',
                'url' => fetchastresources("admin/Data Protection/STEER Privacy Policy for Students_Take The Wheel.pdf")
            ],
            [
                'title' => 'STEER Client Security Policy Statement',
                'url' => fetchastresources("admin/Data Protection/STEER Client Security Policy Statement.pdf")
            ],
        ];
        return $final_response;
    }

    function astnextbookletresources(){
        $final_response['booklet'] = [
            [
                'title' => 'Ready to Launch STEER Tracking',
                'url' => fetchastresources("booklet/Your Launch Year.pdf"),
            ],
            [
                'title' => 'Start the Year with The New STEER Platform',
                'url' => fetchastresources("booklet/Starting the Year.pdf")
            ],
            [
                'title' => 'IT Lead Document',
                'url' => fetchastresources("booklet/IT Booklet.pdf")
            ],
        ];

        return $final_response;
    }
}
