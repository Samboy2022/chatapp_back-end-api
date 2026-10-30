<?php

/**
 * Starter CBT question bank: original practice questions, so the feature
 * works before real past questions are imported. They carry no exam tag (so
 * every exam can use them) and no year (they aren't from a past paper).
 *
 * subject name => [topic name => [[question, [A, B, C, D], correct, explanation], ...]]
 */
return [
    'English Language' => [
        'Grammar' => [
            ['Choose the option that best completes the sentence: Neither the teacher nor the students ___ in the hall.', ['is', 'are', 'was', 'has been'], 'B', 'With "neither … nor", the verb agrees with the nearer subject, "students", which is plural.'],
            ['Choose the correctly punctuated sentence.', ['Its raining, take you\'re umbrella.', 'It\'s raining; take your umbrella.', 'Its raining; take your umbrella.', 'It\'s raining take your umbrella.'], 'B', '"It\'s" is short for "it is", "your" shows possession, and a semicolon joins two independent clauses.'],
            ['Choose the option with the dangling modifier avoided.', ['Running to the gate, the bus left me.', 'Running to the gate, I missed the bus.', 'The bus, running to the gate, left.', 'Running, the gate and the bus left.'], 'B', 'The opening phrase must describe the subject that follows it: "I" was running, not the bus.'],
        ],
        'Vocabulary' => [
            ['Choose the word nearest in meaning to the underlined word: The manager was very FRUGAL with the company\'s money.', ['generous', 'careless', 'thrifty', 'wasteful'], 'C', 'Frugal means careful not to waste money, i.e. thrifty.'],
            ['Choose the word opposite in meaning to: The witness gave a VAGUE account of the accident.', ['clear', 'false', 'long', 'confused'], 'A', 'Vague means unclear; its opposite is clear.'],
            ['Which pair are homophones?', ['bear / beer', 'flour / flower', 'hat / hate', 'cut / cute'], 'B', 'Flour and flower are pronounced the same but spelt differently and mean different things.'],
        ],
        'Oral English' => [
            ['Which word has the same vowel sound as SEAT?', ['sit', 'set', 'key', 'sat'], 'C', '"Key" has the long /iː/ vowel, as in "seat".'],
            ['Choose the word with the stress on the second syllable.', ['TAble', 'beGIN', 'WINdow', 'PAper'], 'B', 'beGIN is stressed on the second syllable; the others on the first.'],
        ],
    ],
    'Mathematics' => [
        'Mensuration' => [
            ['The volume of a cube of side 3 cm is:', ['9 cm³', '18 cm³', '27 cm³', '81 cm³'], 'C', 'Volume of a cube = side³ = 3 × 3 × 3 = 27 cm³.'],
            ['Find the area of a circle of radius 7 cm. (Take π = 22/7)', ['44 cm²', '154 cm²', '88 cm²', '308 cm²'], 'B', 'Area = πr² = 22/7 × 7 × 7 = 154 cm².'],
        ],
        'Algebra' => [
            ['Solve for x: 3x − 7 = 11', ['4', '6', '7', '−6'], 'B', '3x = 18, so x = 6.'],
            ['Factorise x² − 9.', ['(x − 3)(x − 3)', '(x + 9)(x − 1)', '(x − 3)(x + 3)', '(x − 9)(x + 1)'], 'C', 'Difference of two squares: a² − b² = (a − b)(a + b).'],
            ['If y = 2x + 5, find y when x = −3.', ['−1', '1', '11', '−11'], 'A', 'y = 2(−3) + 5 = −6 + 5 = −1.'],
        ],
        'Number and Numeration' => [
            ['Express 0.000452 in standard form.', ['4.52 × 10⁻⁴', '4.52 × 10⁻³', '45.2 × 10⁻⁵', '4.52 × 10⁴'], 'A', 'Move the decimal point four places right: 4.52 × 10⁻⁴.'],
            ['Find the LCM of 12 and 18.', ['6', '36', '72', '216'], 'B', '12 = 2² × 3 and 18 = 2 × 3²; LCM = 2² × 3² = 36.'],
            ['Convert 101₂ to base ten.', ['3', '5', '6', '101'], 'B', '1 × 2² + 0 × 2 + 1 = 5.'],
        ],
    ],
    'Physics' => [
        'Mechanics' => [
            ['A car moves from rest with a uniform acceleration of 2 m/s². What is its speed after 5 s?', ['2.5 m/s', '7 m/s', '10 m/s', '25 m/s'], 'C', 'v = u + at = 0 + 2 × 5 = 10 m/s.'],
            ['The SI unit of force is the:', ['joule', 'newton', 'watt', 'pascal'], 'B', 'Force is measured in newtons (kg·m/s²).'],
            ['A body of mass 4 kg moves with a velocity of 3 m/s. Its kinetic energy is:', ['6 J', '12 J', '18 J', '36 J'], 'C', 'KE = ½mv² = ½ × 4 × 3² = 18 J.'],
        ],
        'Waves and Optics' => [
            ['A wave has a frequency of 50 Hz and a wavelength of 4 m. Its speed is:', ['12.5 m/s', '54 m/s', '200 m/s', '0.08 m/s'], 'C', 'v = fλ = 50 × 4 = 200 m/s.'],
            ['The image formed by a plane mirror is:', ['real and inverted', 'virtual and laterally inverted', 'real and magnified', 'virtual and diminished'], 'B', 'A plane mirror forms a virtual, upright, laterally inverted image the same size as the object.'],
        ],
        'Electricity' => [
            ['A current of 2 A flows through a 6 Ω resistor. The potential difference across it is:', ['3 V', '8 V', '12 V', '0.33 V'], 'C', 'Ohm\'s law: V = IR = 2 × 6 = 12 V.'],
            ['Two 4 Ω resistors are connected in parallel. Their effective resistance is:', ['2 Ω', '4 Ω', '8 Ω', '16 Ω'], 'A', '1/R = 1/4 + 1/4 = 1/2, so R = 2 Ω.'],
        ],
    ],
    'Chemistry' => [
        'Atomic Structure' => [
            ['The number of protons in an atom is its:', ['mass number', 'atomic number', 'neutron number', 'valency'], 'B', 'The atomic number is the number of protons in the nucleus.'],
            ['An atom with 11 protons and 12 neutrons has a mass number of:', ['11', '12', '23', '1'], 'C', 'Mass number = protons + neutrons = 11 + 12 = 23.'],
        ],
        'Acids, Bases and Salts' => [
            ['A solution with a pH of 3 is:', ['strongly alkaline', 'neutral', 'acidic', 'weakly alkaline'], 'C', 'pH below 7 is acidic.'],
            ['The reaction between an acid and a base to form salt and water is called:', ['hydrolysis', 'neutralisation', 'oxidation', 'esterification'], 'B', 'Acid + base → salt + water is neutralisation.'],
        ],
        'Chemical Bonding' => [
            ['The bond in sodium chloride is:', ['covalent', 'ionic', 'metallic', 'hydrogen'], 'B', 'Sodium transfers an electron to chlorine, forming Na⁺ and Cl⁻ ions held by ionic bonds.'],
            ['Which of these molecules contains a double bond?', ['H₂', 'Cl₂', 'O₂', 'HCl'], 'C', 'The two oxygen atoms share two pairs of electrons: O=O.'],
        ],
        'Stoichiometry' => [
            ['How many moles are in 36 g of water? (H = 1, O = 16)', ['1', '2', '3', '18'], 'B', 'Molar mass of H₂O = 18 g/mol; 36 ÷ 18 = 2 mol.'],
        ],
    ],
    'Biology' => [
        'Cell Biology' => [
            ['The powerhouse of the cell is the:', ['nucleus', 'ribosome', 'mitochondrion', 'vacuole'], 'C', 'Mitochondria release energy through aerobic respiration.'],
            ['Which structure is found in plant cells but not in animal cells?', ['cell membrane', 'cell wall', 'cytoplasm', 'nucleus'], 'B', 'Plant cells have a cellulose cell wall; animal cells do not.'],
        ],
        'Nutrition' => [
            ['The green pigment that traps light energy in plants is:', ['haemoglobin', 'chlorophyll', 'melanin', 'carotene'], 'B', 'Chlorophyll absorbs light for photosynthesis.'],
            ['Deficiency of vitamin C causes:', ['rickets', 'scurvy', 'night blindness', 'beriberi'], 'B', 'Lack of vitamin C causes scurvy (bleeding gums, poor wound healing).'],
        ],
        'Genetics' => [
            ['A cross between two heterozygous tall plants (Tt × Tt) gives a phenotypic ratio of:', ['1 : 1', '3 : 1', '1 : 2 : 1', '9 : 3 : 3 : 1'], 'B', 'Offspring are TT, Tt, Tt, tt: three tall to one short.'],
            ['The basic unit of heredity is the:', ['chromosome', 'gene', 'nucleus', 'allele pair'], 'B', 'Genes carry hereditary information.'],
        ],
        'Ecology' => [
            ['In a food chain, green plants are:', ['primary consumers', 'producers', 'decomposers', 'secondary consumers'], 'B', 'Green plants make their own food and so are producers.'],
        ],
    ],
    'Economics' => [
        'Demand and Supply' => [
            ['Other things being equal, a rise in the price of a good leads to:', ['a rise in quantity demanded', 'a fall in quantity demanded', 'a rise in demand', 'no change in demand'], 'B', 'The law of demand: price and quantity demanded move in opposite directions.'],
            ['Goods that are used together, such as cars and petrol, are:', ['substitutes', 'complements', 'inferior goods', 'Giffen goods'], 'B', 'Complementary goods are jointly demanded.'],
        ],
        'Basic Concepts' => [
            ['The alternative forgone when a choice is made is called:', ['scale of preference', 'opportunity cost', 'scarcity', 'real cost'], 'B', 'Opportunity cost is the next best alternative given up.'],
            ['Which of these is a factor of production?', ['money', 'land', 'profit', 'interest'], 'B', 'The factors of production are land, labour, capital and entrepreneurship.'],
        ],
        'Money and Banking' => [
            ['The bank that regulates other banks in Nigeria is the:', ['First Bank', 'Central Bank of Nigeria', 'Bank of Industry', 'World Bank'], 'B', 'The Central Bank of Nigeria supervises and regulates banks.'],
            ['Inflation is a persistent rise in the:', ['general price level', 'level of employment', 'value of money', 'rate of exports'], 'A', 'Inflation is a sustained increase in the general level of prices.'],
        ],
    ],
    'Government' => [
        'Concepts of Government' => [
            ['The ability to make others obey commands is:', ['authority', 'power', 'legitimacy', 'sovereignty'], 'B', 'Power is the capacity to compel obedience; authority is the right to exercise it.'],
            ['Supreme power of a state over its territory is called:', ['democracy', 'sovereignty', 'franchise', 'federalism'], 'B', 'Sovereignty is the supreme and independent authority of the state.'],
        ],
        'Constitution and Structures' => [
            ['The principle that separates the legislature, executive and judiciary is:', ['rule of law', 'separation of powers', 'delegated legislation', 'judicial review'], 'B', 'Separation of powers divides government into three arms.'],
            ['A constitution that is difficult to amend is said to be:', ['flexible', 'rigid', 'unwritten', 'unitary'], 'B', 'A rigid constitution needs a special procedure to amend.'],
        ],
        'Elections' => [
            ['The right to vote is known as:', ['franchise', 'referendum', 'recall', 'manifesto'], 'A', 'Franchise (suffrage) is the right to vote.'],
            ['A vote by the whole electorate on a single issue is a:', ['by-election', 'referendum', 'primary election', 'plebiscite of candidates'], 'B', 'A referendum puts a specific question directly to voters.'],
        ],
    ],
];
