{
    "_id": "_design/author",
    "views": {
        "literal": {
            "map": "function (doc) {\n  var csl = doc.message;\n  if (csl.author) {\n    for (var j in csl.author) {\n       var literal = '';\n       if (csl.author[j].literal) {\n         literal = csl.author[j].literal;\n       } else {\n          var parts = [];\n          if (csl.author[j].given) {\n            parts.push(csl.author[j].given);\n          }\n          if (csl.author[j].family) {\n            parts.push(csl.author[j].family);\n          }\n          literal = parts.join(' ');\n       }\n       emit(doc._id, literal);\n    }\n  }\n}"
        },
        "family-given": {
            "map": "function (doc) {\n  var csl = doc.message;\n  if (csl.author) {\n    for (var j in csl.author) {\n       if (csl.author[j].family && csl.author[j].given) {\n          emit([csl.author[j].family, csl.author[j].given], 1);\n       }\n    }\n  }\n}",
            "reduce": "_sum"
        },
        "identifier": {
            "map": "function (doc) {\n  var csl = doc.message;\n  if (csl.author) {\n    for (var j in csl.author) {\n       var literal = '';\n       if (csl.author[j].literal) {\n         literal = csl.author[j].literal;\n       } else {\n          var parts = [];\n          if (csl.author[j].given) {\n            parts.push(csl.author[j].given);\n          }\n          if (csl.author[j].family) {\n            parts.push(csl.author[j].family);\n          }\n          literal = parts.join(' ');\n       }\n       \n       if (csl.author[j].ORCID) {\n         emit('ORCID:' + csl.author[j].ORCID.replace(/http[s]?:\\/\\/orcid.org\\//, ''), literal);\n       }\n       \n       if (csl.author[j].WIKISPECIES) {\n         emit('WIKISPECIES:' + csl.author[j].WIKISPECIES.replace(/\\s+/g, '_'), literal);\n       }       \n    }\n  }\n}"
        },
        "language_pairs": {
            "map": "   \nfunction (doc) {\n  var csl = doc.message;\n  if (csl.author) {\n    for (var j in csl.author) {\n      if (csl.author[j].multi) {\n        for (var k in csl.author[j].multi._key) {\n          switch (k) {\n            case 'name':\n              for (var x in csl.author[j].multi._key[k]) {\n                for (var y in csl.author[j].multi._key[k]) {\n                  if (x != y) {\n                    emit(csl.author[j].multi._key[k][x],csl.author[j].multi._key[k][y]);\n                  }\n                }\n              }\n              break;\n               \n             default:\n               break;        \t \n        \t }\n        }\n        \n        \n      }\n    } \n  }\n} "
        }
    },
    "language": "javascript"
}