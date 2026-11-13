{{* Home Base widget presentation; MIT Expat License, copyright 2026 WisTex TechSero Ltd. Co. *}}
<section class="homebase-widget card mb-3">
  {{if $title}}<div class="card-header"><h3 class="h6 mb-0">{{$title}}</h3></div>{{/if}}
  <div class="card-body">
    <ul class="nav {{if $horizontal}}nav-pills flex-row flex-wrap gap-2{{else}}flex-column gap-1{{/if}}">
    {{foreach $items as $item}}
      <li class="nav-item">
        {{if $item.url}}
          <a class="nav-link {{if !$horizontal}}rounded-2{{/if}}" href="{{$item.url}}" title="{{$item.description}}">
            {{$item.label}}
          </a>
        {{else}}
          <span class="nav-link disabled" title="{{$item.description}}">{{$item.label}}</span>
        {{/if}}
        {{if !$horizontal && $descriptions}}<div class="small text-muted px-3 pb-2">{{$item.description}}</div>{{/if}}
      </li>
    {{/foreach}}
    </ul>
  </div>
</section>
