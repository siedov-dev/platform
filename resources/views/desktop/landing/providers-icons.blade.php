@php
    $groups = \App\GamesProviders\GamesManager::getAllGroups();
@endphp
<section class="providers-icons">
    @foreach($groups as $group)
        <a class="providers-icon" href="/games?providers={{$group->getProviderId()}}%7C{{$group->getId()}}&category={{implode(',',$group->getAllCategories())}}">
            <img src="/img/icons/providers/{{$group->getProviderId()}}/{{str_replace(' ', '_', $group->getName())}}.png">
        </a>
    @endforeach
</section>
