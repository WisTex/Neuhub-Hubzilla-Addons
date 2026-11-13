{{* Bootstrap 5 common navigation; MIT Expat License, copyright 2026 WisTex TechSero Ltd. Co. *}}
<nav class="navbar navbar-expand-lg bg-body-tertiary rounded mb-3" aria-label="Common navigation">
  <div class="container-fluid">
    <a class="navbar-brand d-flex align-items-center gap-2" href="{{$channel_url}}" title="{{$channel_name}}" aria-label="{{$channel_name}} — view channel">
      {{if $avatar}}<img src="{{$avatar}}" alt="" width="36" height="36" class="rounded-circle object-fit-cover">{{/if}}
      <span>{{$title}}</span>
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#{{$collapse_id}}" aria-controls="{{$collapse_id}}" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="{{$collapse_id}}">
      <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
        {{foreach $items as $item}}
          <li class="nav-item">
            <a class="nav-link{{if $item.active}} active{{/if}}" {{if $item.active}}aria-current="page"{{/if}} href="{{$item.url}}">{{$item.label}}</a>
          </li>
        {{/foreach}}
      </ul>
    </div>
  </div>
</nav>
