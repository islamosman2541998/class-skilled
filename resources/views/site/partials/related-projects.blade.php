{{-- Related projects glass slider on paper (reuses the home projects section markup). --}}
@include('site.sections.projects', ['section' => [
    'id' => 'related',
    'type' => 'projects',
    'background' => 'paper',
    'bg_image' => null,
    'title' => $title,
    'subtitle' => '',
    'cta' => isset($link) ? $link + ['target_blank' => false] : null,
    'display' => 'slider',
    'autoplay' => false,
    'items' => $items,
    'show_filters' => false,
]])
