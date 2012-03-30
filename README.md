# Intro

I apologyze for my English. I've been using minion, and it's excellent.
I thought once that Kohana is not enough mechanisms of automatic generation
of simple structures, and has written several tasks.

I use them to automatically generate:

* Controllers,
* Form views,
* Models

## Forms

It's no secret that within the same site many forms run on similar principles,
which means they can be easily and automatically generate. Ok, look this:

`minion autogen:form --filename=somefile --fields=username:text,password:password,userinfo:textarea`

I think is not needed further explanations.
File will be created in `APPPATH/views/somefile.php`.

## Controllers

With the controller is also simple:

`minion autogen:controller --name=good --actions=index,add,edit,delete --extends=controller_template_page`

Or, if you need to create multiple controllers:

`minion autogen:controller --name=main,feedback,news,articles --actions=index --extends=controller_template_page`

## Models

Now to the fun - models. Suppose we have a table of `categories`,
it has a lot of fields, and we need to create a model. Of course,
we can do it manually ... and can be done automatically and avoid routine.

`minion autogen:model --name=category`

At the same time, table fields will be analyzed types automatically and taken
into account in the filters and rules.