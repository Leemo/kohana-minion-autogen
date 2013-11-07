# Intro

I apologyze for my English. I've been using minion, and it's excellent.
I thought once that Kohana is not enough mechanisms of automatic generation
of simple structures, and has written several tasks.

I use them to automatically generate:

* Controllers,
* Form views,
* Models
* Messages

## Forms

It's no secret that within the same site many forms run on similar principles,
which means they can be easily and automatically generate. Ok, look this:

`minion autogen:form`

I think is not needed further explanations.
File will be created in `APPPATH/views/somefile.php`.

## Controllers

With the controller is also simple:

`minion autogen:controller`

Or, if you need to create multiple controllers:

`minion autogen:controller`

## Models

Now to the fun - models. Suppose we have a table of `categories`,
it has a lot of fields, and we need to create a model. Of course,
we can do it manually ... and can be done automatically and avoid routine.

`minion autogen:model --name=category`

At the same time, table fields will be analyzed types automatically and taken
into account in the filters and rules.

## Messages

See http://kohanaframework.org/3.3/guide/orm/validation#handling-validation-exceptions

`minion autogen:message`

Creates (or updates, if they already exist) message files.