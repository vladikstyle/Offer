<div uib-carousel>
    <div class="encounter-photo-item h-100" uib-slide ng-repeat="photo in currentEncounter.photos track by photo.id"
         index="$index"
         ng-swipe-left="$parent.$parent.prev()"
         ng-swipe-right="$parent.$parent.next()"
         ng-click="$parent.$parent.next()">
        <div class="carousel-photo h-100">
            <img ng-src="{{ photo.url }}" class="carousel-photo-img">
        </div>
    </div>
</div>
