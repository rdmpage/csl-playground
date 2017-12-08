{
    "_id": "_design/link",
    "views": {
        "pdf": {
            "map": "function(doc) {\n  var csl = doc.message;\n          if (csl.link) {\n            for (var j in csl.link) {\n              if (csl.link[j]['content-type'] == 'application/pdf') {\n                emit(csl.link[j].URL, 1);\n              }\n            }\n         }\n}",
            "reduce": "_sum"
        },
        "pdf-domain": {
            "map": "function(doc) {\n  var csl = doc.message;\n          if (csl.link) {\n            for (var j in csl.link) {\n              if (csl.link[j]['content-type'] == 'application/pdf') {\n                result = csl.link[j].URL.match(/^https?:\\/\\/(([a-zA-Z\\d-]+\\.)+[a-zA-Z\\d-]+)/);\n                if (result) {\n                   emit(result[1], 1);\n                } \n    \n              }\n            }\n         }\n}",
            "reduce": "_sum"
        }
    },
    "language": "javascript"
}