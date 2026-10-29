window.doc_page = {
    "addon": "Low Random",
    "title": "Tag Reference",
    "desc": "<p>Choose a random file, item, letter or number. The File, Item, Letter and Number tags are single tags; Items is a tag pair. See <a href=\"/low-random/documentation/installation\">Installation</a> for setup.</p>",
    "terms": [
        {
            "label": "required",
            "description": "Supply this parameter for the tag to choose from the intended source."
        }
    ],
    "sections": [
        {
            "type": "tagtoc"
        },
        {
            "type": "tags"
        }
    ],
    "tags": [
        {
            "tag": "{exp:low_random:file}",
            "shortname": "exp:low_random:file",
            "desc": "Select one matching file from a server folder or an ExpressionEngine upload destination.",
            "sections": [
                {
                    "type": "params",
                    "title": "Parameters",
                    "items": [
                        {
                            "item": "folder",
                            "desc": "A server directory path or numeric Upload Destination ID. A server directory returns a filename; an Upload Destination ID selects from registered File records and returns the destination URL plus filename.",
                            "type": "string",
                            "required": true,
                            "examples": [
                                {
                                    "tag_example": "{exp:low_random:file folder=\"/some/path/to/images\" filter=\"masthead|.jpg\"}"
                                }
                            ]
                        },
                        {
                            "item": "filter",
                            "desc": "Substrings that the filename must contain. Separate multiple filters with `|`; every filter must match. Server-directory matching uses literal, case-sensitive substrings; Upload Destination matching uses the database’s LIKE comparison.",
                            "type": "string",
                            "default": "no filter",
                            "examples": [
                                {
                                    "tag_example": "{exp:low_random:file folder=\"3\" filter=\".pdf\"}"
                                }
                            ]
                        }
                    ]
                }
            ]
        },
        {
            "tag": "{exp:low_random:item}",
            "shortname": "exp_low_random_item",
            "anchor": "tag-exp_",
            "desc": "Return one item chosen from a pipe-separated list.",
            "sections": [
                {
                    "type": "params",
                    "title": "Parameters",
                    "items": [
                        {
                            "item": "items",
                            "desc": "The items to choose from, separated by `|`.",
                            "type": "string",
                            "examples": [
                                {
                                    "tag_example": "{exp:low_random:item items=\"cat|dog|ferret|raptor\"}"
                                }
                            ]
                        }
                    ]
                },
                {
                    "type": "examples",
                    "title": "Usage",
                    "items": [
                        {
                            "item": "Another list",
                            "desc": "Choose one response from the list, using the existing example from Low’s original guide.",
                            "examples": [
                                {
                                    "tag_example": "{exp:low_random:item items=\"Yes|No|Maybe|Try again later|You're kidding, right?\"}"
                                }
                            ]
                        }
                    ]
                }
            ]
        },
        {
            "tag": "{exp:low_random:items}",
            "shortname": "exp_low_random_items",
            "desc": "Return one item chosen from the contents of the tag pair.",
            "sections": [
                {
                    "type": "examples",
                    "title": "Usage",
                    "items": [
                        {
                            "item": "One item per line",
                            "desc": "The default separator is a newline. Keep incidental indentation out of the individual values if you do not want it returned.",
                            "examples": [
                                {
                                    "tag_example": "{exp:low_random:items}\nCat\nDog\nFerret\nRaptor\n{/exp:low_random:items}"
                                }
                            ]
                        }
                    ]
                },
                {
                    "type": "params",
                    "title": "Parameters",
                    "items": [
                        {
                            "item": "separator",
                            "desc": "The non-empty string used to split the tag-pair contents into choices.",
                            "type": "string",
                            "default": "newline",
                            "examples": [
                                {
                                    "tag_example": "{exp:low_random:items separator=\",\"}Cat,Dog,Ferret,Raptor{/exp:low_random:items}"
                                }
                            ]
                        },
                        {
                            "item": "trim",
                            "desc": "By default, remove whitespace from the beginning and end of the complete tag-pair contents before splitting. Set `no` to retain that surrounding whitespace. This does not trim every individual item.",
                            "type": "string",
                            "default": "yes",
                            "examples": [
                                {
                                    "tag_example": "{exp:low_random:items separator=\"|\" trim=\"no\"} Cat | Dog | Ferret {/exp:low_random:items}"
                                }
                            ],
                            "accepts": "yes, no"
                        }
                    ]
                }
            ]
        },
        {
            "tag": "{exp:low_random:letter}",
            "shortname": "exp_low_random_letter",
            "desc": "Return a letter from the supplied range, using the case of the supplied endpoints.",
            "sections": [
                {
                    "type": "examples",
                    "title": "Usage",
                    "items": [
                        {
                            "item": "Default letter range",
                            "desc": "Choose one lowercase letter from a through z.",
                            "examples": [
                                {
                                    "tag_example": "{exp:low_random:letter}"
                                }
                            ]
                        }
                    ]
                },
                {
                    "type": "params",
                    "title": "Parameters",
                    "items": [
                        {
                            "item": "from",
                            "desc": "The first letter in the range.",
                            "type": "string",
                            "default": "a",
                            "examples": [
                                {
                                    "tag_example": "{exp:low_random:letter from=\"A\" to=\"F\"}"
                                }
                            ]
                        },
                        {
                            "item": "to",
                            "desc": "The last letter in the range.",
                            "type": "string",
                            "default": "z",
                            "examples": [
                                {
                                    "tag_example": "{exp:low_random:letter from=\"A\" to=\"F\"}"
                                }
                            ]
                        }
                    ]
                }
            ]
        },
        {
            "tag": "{exp:low_random:number}",
            "shortname": "exp_low_random_number",
            "desc": "Return a number from the supplied range.",
            "sections": [
                {
                    "type": "examples",
                    "title": "Usage",
                    "items": [
                        {
                            "item": "Default number range",
                            "desc": "Choose one number from 0 through 9.",
                            "examples": [
                                {
                                    "tag_example": "{exp:low_random:number}"
                                }
                            ]
                        }
                    ]
                },
                {
                    "type": "params",
                    "title": "Parameters",
                    "items": [
                        {
                            "item": "from",
                            "desc": "The first number in the range.",
                            "type": "number",
                            "default": "0",
                            "examples": [
                                {
                                    "tag_example": "{exp:low_random:number from=\"100\" to=\"999\"}"
                                }
                            ]
                        },
                        {
                            "item": "to",
                            "desc": "The last number in the range.",
                            "type": "number",
                            "default": "9",
                            "examples": [
                                {
                                    "tag_example": "{exp:low_random:number from=\"100\" to=\"999\"}"
                                }
                            ]
                        }
                    ]
                }
            ]
        }
    ]
};
