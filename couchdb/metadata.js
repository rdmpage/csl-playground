{
    "_id": "_design/metadata",
    "views": {
        "title": {
            "map": "function (doc) {\n  var csl = doc.message;\n  \n  if (csl.title) {\n\t\tif (Array.isArray(csl.title)) {\n\t\t\tfor (var j in csl.title) {\n\t\t      emit(doc._id, csl.title[j]);\n\t\t\t  }\n\t  } else {\n\t\t\t emit(doc._id, csl.title);\n\t\t}\n  }\n}\n"
        },
        "container-title": {
            "map": "function (doc) {\n  var csl = doc.message;\n  \n  if (csl['container-title']) {\n\t\tif (Array.isArray(csl['container-title'])) {\n\t\t\tfor (var j in csl['container-title']) {\n\t\t      emit(csl['container-title'][j], 1);\n\t\t\t  }\n\t  } else {\n\t\t\t emit(csl['container-title'], 1);\n\t\t}\n  }\n}\n",
            "reduce": "_sum"
        },
        "year": {
            "map": "function (doc) {\n  var csl = doc.message;\n  \n  if (csl.issued) {\n    var year = csl.issued['date-parts'][0][0];\n    if (year) {\n\t\t\temit(year, 1);\n    }\n\t}\n}\n",
            "reduce": "_sum"
        },
        "language_title": {
            "map": "function (doc) {\n  var csl = doc.message;\n\n  if (csl.multi) {\n\tfor (var j in csl.multi._key) {\n\t  for (var k in csl.multi._key[j]) {\n\t\tswitch (j) {\n\t\t  case 'title':\n\t\t\temit(j, [k, csl.multi._key[j][k]]);\n\t\t\tbreak;\n\t\t   default:\n\t\t\t break;\n\t\t}\n\t  }\n\t}               \n  }\n\n}\n"
        },
        "language_abstract": {
            "map": "function (doc) {\n  var csl = doc.message;\n\n  if (csl.multi) {\n\tfor (var j in csl.multi._key) {\n\t  for (var k in csl.multi._key[j]) {\n\t\tswitch (j) {\n\t\t  case 'abstract':\n\t\t\temit(j, [k, csl.multi._key[j][k]]);\n\t\t\tbreak;\n\t\t   default:\n\t\t\t break;\n\t\t}\n\t  }\n\t}               \n  }\n\n}\n"
        }
    },
    "language": "javascript"
}