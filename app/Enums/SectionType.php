<?php

namespace App\Enums;

use App\Enums\Concerns\HasLabel;

enum SectionType: string
{
    use HasLabel;

    case Hero = 'hero';
    case Facts = 'facts';
    case About = 'about';
    case Services = 'services';
    case FeatureBand = 'feature_band';
    case Projects = 'projects';
    case WhyUs = 'why_us';
    case Process = 'process';
    case Stats = 'stats';
    case Testimonials = 'testimonials';
    case Clients = 'clients';
    case Posts = 'posts';
    case Certificates = 'certificates';
    case Cta = 'cta';
    case RichText = 'rich_text';
}
