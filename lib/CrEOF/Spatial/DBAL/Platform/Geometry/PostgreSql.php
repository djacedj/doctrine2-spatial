<?php
/**
 * Copyright (C) 2015 Derek J. Lambert
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

namespace CrEOF\Spatial\DBAL\Platform\Geometry;

use CrEOF\Spatial\DBAL\Platform\AbstractPlatform;
use CrEOF\Spatial\Exception\InvalidValueException;
use CrEOF\Spatial\PHP\Types\Geometry\GeometryInterface;

/**
 * PostgreSql spatial platform
 *
 * @author  Derek J. Lambert <dlambert@dereklambert.com>
 * @license http://dlambert.mit-license.org MIT
 */
class PostgreSql extends AbstractPlatform
{
    /**
     * Get the type family for this interface (i.e. geometry or geography)
     *
     * @return string
     */
    public function getTypeFamily()
    {
        return GeometryInterface::GEOMETRY;
    }

    /**
     * Gets the SQL declaration snippet for a field of this type.
     *
     * @param array $fieldDeclaration
     *
     * @return string
     */
    public function getSQLDeclaration(array $fieldDeclaration)
    {
        if ($fieldDeclaration['type']->getSQLType() == GeometryInterface::GEOMETRY) {
            return 'geometry';
        }

        return sprintf('geometry(%s)', $fieldDeclaration['type']->getSQLType());
    }

    /**
     * @param string $sqlExpr
     *
     * @return string
     */
    public function convertToPHPValueSQL($sqlExpr)
    {
        return sprintf('ST_AsEWKB(%s)', $sqlExpr);
    }

    /**
     * @param string $sqlExpr
     *
     * @return string
     */
    public function convertToDatabaseValueSQL($sqlExpr)
    {
        return sprintf('ST_GeomFromEWKT(%s)', $sqlExpr);
    }

    /**
     * @param string $sqlExpr
     *
     * @return GeometryInterface
     * @throws InvalidValueException
     */
    public function convertBinaryToPHPValue($sqlExpr)
    {
        if (! is_resource($sqlExpr)) {
            throw new InvalidValueException(sprintf('Invalid resource value "%s"', $sqlExpr));
        }

        $sqlExpr = stream_get_contents($sqlExpr);

        return parent::convertBinaryToPHPValue($sqlExpr);
    }

    /**
     * @param GeometryInterface $value
     *
     * @return string
     */
    public function convertToDatabaseValue(GeometryInterface $value)
    {
        $sridSQL = null;

        if (($srid = $value->getSrid()) !== null) {
            $sridSQL = sprintf('SRID=%d;', $srid);
        }

        return sprintf('%s%s', $sridSQL, parent::convertToDatabaseValue($value));
    }
}
