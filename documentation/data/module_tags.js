window.doc_page = {
    addon: 'Low Random',
    title: 'Tags',
    sections: [
        {
            title: '',
            type: 'tagtoc',
            desc: 'Low Random has the following front-end tags: ',
        },
        {
            title: '',
            type: 'tags',
            desc: ''
        },
    ],
    tags: [
        {
            tag: '{exp:low_random:file}',
            shortname: 'exp:low_random:file',
            summary: "",
            desc: "",
            sections: [
                {
                    type: 'params',
                    title: 'Tag Parameters',
                    desc: '',
                    items: [
                        {
                            item: 'folder',
                            desc: '	Either the server path of the folder, or the numeric Upload Destination id. Required.',
                            type: '',
                            accepts: '',
                            default: '',
                            required: true,
                            added: '',
                            examples: [
                                {
                                    tag_example: `{exp:low_random:file folder="/some/path/to/images" filter="masthead|.jpg"}`,
                                    outputs: ``
                                 }
                             ]
                        },

                        {
                            item: 'filter',
                            desc: '	Any number of sub strings the file name should contain, separated by vertical bars.',
                            type: '',
                            accepts: '',
                            default: '',
                            required: false,
                            added: '',
                            examples: [
                                {
                                    tag_example: `{exp:low_random:file folder="3" filter=".pdf"}`,
                                    outputs: ``
                                 }
                             ]
                        },
                        
                      
                    ]
                }
            ]
        },

        {
            tag: 'exp:low_random:item',
            shortname: 'exp_',
            summary: "",
            desc: "",
            sections: [
                {
                    type: 'params',
                    title: 'Tag Parameters',
                    desc: '',
                    items: [
                        {
                            item: 'items',
                            desc: 'Any number of items, separated by vertical bars.',
                            type: '',
                            accepts: '',
                            default: '',
                            required: false,
                            added: '',
                            examples: [
                                {
                                    tag_example: `{exp:low_random:item items="cat|dog|ferret|raptor"}`,
                                    outputs: ``
                                 }
                             ]
                        },
                        
                      
                    ]
                }
            ]
        },

        {
            tag: '{exp:low_random:items}',
            shortname: 'exp_',
            summary: "",
            desc: "",
            sections: [
                {
                    type: 'params',
                    title: 'Tag Parameters',
                    desc: '',
                    items: [
                        {
                            item: 'separator',
                            desc: 'Character used to separate values. Defaults to new line.',
                            type: '',
                            accepts: '',
                            default: '',
                            required: false,
                            added: '',
                            examples: [
                                {
                                    tag_example: `
                                    {exp:low_random:items separator=","}
                                        Cat, Dog, Ferret, Raptor
                                    {/exp:low_random:items}`,
                                    outputs: ``
                                 }
                             ]
                        },
                        {
                            item: 'trim',
                            desc: '	Set to “no” if you don’t want the tagdata to be stripped of white space at the beginning and end.',
                            type: '',
                            accepts: '',
                            default: '',
                            required: false,
                            added: '',
                            examples: [
                                {
                                    tag_example: ``,
                                    outputs: ``
                                 }
                             ]
                        },
                        
                      
                    ]
                }
            ]
        },

        {
            tag: '{exp:low_random:letter}',
            shortname: 'exp_',
            summary: "",
            desc: "Note: this tag returns a letter in the same case as the given parameters.",
            sections: [
                {
                    type: 'params',
                    title: 'Tag Parameters',
                    desc: '',
                    items: [
                        {
                            item: 'from',
                            desc: '	Letter to start the range with, defaults to a',
                            type: '',
                            accepts: '',
                            default: 'a',
                            required: false,
                            added: '',
                            examples: [
                                {
                                    tag_example: `{exp:low_random:letter from="A" to="F"}`,
                                    outputs: ``
                                 }
                             ]
                        },
                        {
                            item: 'to',
                            desc: 'Letter to end the range with, defaults to z.',
                            type: '',
                            accepts: '',
                            default: 'z',
                            required: false,
                            added: '',
                            examples: [
                                {
                                    tag_example: ``,
                                    outputs: ``
                                 }
                             ]
                        },
                        
                      
                    ]
                }
            ]
        },

        {
            tag: '{exp:low_random:number}',
            shortname: 'exp_',
            summary: "",
            desc: "",
            sections: [
                {
                    type: 'params',
                    title: 'Tag Parameters',
                    desc: '',
                    items: [
                        {
                            item: 'from	',
                            desc: '	Number to start the range with, defaults to 0.',
                            type: '',
                            accepts: '',
                            default: '0',
                            required: false,
                            added: '',
                            examples: [
                                {
                                    tag_example: `{exp:low_random:number from="100" to="999"}`,
                                    outputs: ``
                                 }
                             ]
                        },
                        {
                            item: 'to',
                            desc: 'Number to end the range with, defaults to 9.',
                            type: '',
                            accepts: '',
                            default: '9',
                            required: false,
                            added: '',
                            examples: [
                                {
                                    tag_example: ``,
                                    outputs: ``
                                 }
                             ]
                        },
                        
                      
                    ]
                }
            ]
        },



    ]
};