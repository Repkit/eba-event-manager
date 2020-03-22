# You can define events and entities defining relations between them


# Endpoints (base /event-manager):
### events [ Id | Identifier* | Name* | DestinationId | StartDate* | EndDate* | Status*  ]
* POST      /events                    -> create new event [ Name*,  Identifier*, StartDate*, EndDate*, Status* ]
* GET       /events                    -> list all events
* GET       /events/*/languages/:language_code                   -> list all events translated in specified lang (ISO2)
* GET       /events/:event_id    -> get specific event info
* GET       /events/:event_id/languages/*    -> get specific event info in all translated languages
* PATCH     /events/:event_id    -> patch specific event (1..n field even if required)
* PATCH     /events/:event_id/languages/:language_code    -> patch specific translation of specific event (1..n field even if required)
* UPDADE    
* DELETE    /events/:event_id    -> DELETES ALL DATA RELATED TO THAT EVENT (translations, details, data, profiles, associations with other entities)
