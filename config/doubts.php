<?php

use Illuminate\Support\Str;

$slug = static function (string $value): string {
    return (string) Str::of($value)
        ->replace(['&'], ' and ')
        ->replace(['–', '—'], ' ')
        ->replace(['(', ')', ',', '.', '/', ':'], ' ')
        ->squish()
        ->lower()
        ->slug('_');
};

$makeSubject = static function (string $label, array $chapters) use ($slug): array {
    $formatted = [
        'label' => $label,
        'chapters' => [],
    ];

    foreach ($chapters as $chapterLabel => $subtopics) {
        $chapterKey = $slug($chapterLabel);

        $formatted['chapters'][$chapterKey] = [
            'label' => $chapterLabel,
            'subtopics' => [],
        ];

        foreach ($subtopics as $subtopicLabel) {
            $subtopicKey = $slug($subtopicLabel);
            $formatted['chapters'][$chapterKey]['subtopics'][$subtopicKey] = $subtopicLabel;
        }
    }

    return $formatted;
};

return [
    'subjects' => [
        'mathematics' => $makeSubject('Mathematics', [
            'Algebra' => [
                'Sets, Relations & Functions',
                'Logarithms',
                'Complex Numbers',
                'Quadratic & Polynomial Equations',
                'Permutations & Combinations',
                'Mathematical Induction',
                'Binomial Theorem',
                'Sequences & Series (AP, GP, HP)',
                'Matrices & Determinants',
                'Statistics (Mean, Variance, Standard Deviation)',
            ],
            'Trigonometry' => [
                'Trigonometric Functions & Identities',
                'Trigonometric Equations',
                'Inverse Trigonometric Functions',
                'Heights & Distances',
            ],
            'Coordinate Geometry' => [
                'Straight Lines',
                'Circles',
                'Parabola',
                'Ellipse',
                'Hyperbola',
            ],
            'Calculus' => [
                'Limits',
                'Continuity & Differentiability',
                'Differentiation',
                'Application of Derivatives',
                'Integration (Definite & Indefinite)',
                'Differential Equations',
            ],
            'Vectors & 3D Geometry' => [
                'Vector Algebra (Dot & Cross Product)',
                '3D Geometry (Lines, Planes, Distance)',
            ],
            'Probability' => [
                'Probability Rules',
                'Conditional Probability',
                'Basic Distributions',
            ],
        ]),

        'physics' => $makeSubject('Physics', [
            'Physical World & Measurement' => [
                'Units and dimensions',
                'Errors & accuracy',
                'Significant figures',
            ],
            'Kinematics' => [
                'Motion in straight line',
                'Motion in plane (projectile, vectors)',
            ],
            'Laws of Motion' => [
                "Newton's laws",
                'Friction',
                'Circular motion',
            ],
            'Work, Energy & Power' => [
                'Work-energy theorem',
                'Conservation of energy',
                'Power',
            ],
            'System of Particles & Rotational Motion' => [
                'Centre of mass',
                'Torque',
                'Angular momentum',
            ],
            'Gravitation' => [
                'Laws of gravitation',
                'Satellites',
            ],
            'Properties of Matter' => [
                'Elasticity',
                'Fluid mechanics',
                'Surface tension',
            ],
            'Thermodynamics' => [
                'Laws of thermodynamics',
                'Heat transfer',
            ],
            'Kinetic Theory of Gases' => [
                'Gas laws',
                'Molecular behavior',
            ],
            'Oscillations & Waves' => [
                'SHM (Simple Harmonic Motion)',
                'Wave motion',
            ],
            'Electrostatics' => [
                'Electric charges & fields',
                'Potential & capacitance',
            ],
            'Current Electricity' => [
                "Ohm's law",
                "Kirchhoff's laws",
                'Circuits',
            ],
            'Magnetic Effects of Current & Magnetism' => [
                'Biot–Savart law',
                "Ampere's law",
                'Magnetic materials',
            ],
            'Electromagnetic Induction & AC' => [
                "Faraday's laws",
                'Alternating current',
            ],
            'Electromagnetic Waves' => [
                'Properties & applications',
            ],
            'Optics' => [
                'Ray optics (mirror, lens)',
                'Wave optics (interference, diffraction)',
            ],
            'Modern Physics' => [
                'Dual nature of matter',
                'Atoms & nuclei',
                'Semiconductor devices',
            ],
        ]),

        'chemistry' => $makeSubject('Chemistry', [
            'Physical Chemistry' => [
                'Some Basic Concepts of Chemistry (mole concept, stoichiometry)',
                'Structure of Atom',
                'States of Matter (Gases & Liquids)',
                'Thermodynamics',
                'Chemical Equilibrium',
                'Redox Reactions',
                'Solid State',
                'Solutions',
                'Electrochemistry',
                'Chemical Kinetics',
                'Surface Chemistry',
            ],
            'Inorganic Chemistry' => [
                'Classification of Elements & Periodicity',
                'Chemical Bonding & Molecular Structure',
                'Hydrogen',
                's-Block Elements',
                'p-Block Elements (Group 13 & 14)',
                'p-Block Elements (Group 15–18)',
                'd-Block Elements (Transition metals)',
                'f-Block Elements (Lanthanides & Actinides)',
                'Coordination Compounds',
                'Environmental Chemistry',
            ],
            'Organic Chemistry' => [
                'Basic Principles & Techniques',
                'Hydrocarbons',
                'Haloalkanes & Haloarenes',
                'Alcohols, Phenols & Ethers',
                'Aldehydes, Ketones & Carboxylic Acids',
                'Amines',
                'Biomolecules',
                'Polymers',
                'Chemistry in Everyday Life',
            ],
        ]),
    ],
];